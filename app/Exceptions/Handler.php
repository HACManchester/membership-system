<?php

namespace BB\Exceptions;

use BB\Entities\User;
use BB\Helpers\TelegramErrorHelper;
use BB\Notifications\ErrorNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException as IlluminateValidationException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        // Laravel 5.5 removed everything in $dontReport... let's see what that does for us.
        // HttpException::class,
        // NotFoundHttpException::class,
        // ModelNotFoundException::class,
        // MethodNotAllowedHttpException::class,
        // FormValidationException::class,
        // AuthenticationException::class,         //These are logged separately below
        // AuthorizationException::class,
        // ModelNotFoundException::class,
        // ValidationException::class,
        // IlluminateValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $e
     * @return void
     * 
     * @throws \Throwable
     */
    public function report(Throwable $e)
    {
        if ($this->shouldReport($e) && app()->bound('sentry')) {
            app('sentry')->captureException($e);
        }

        if ($this->shouldReport($e)) {
            $this->telegramException($e);
        }

        //The parent will log exceptions that aren't of the types above
        parent::report($e);
    }

    protected function telegramException(Throwable $e)
    {
        try {
            $level = 'error';
            $title = 'Error';
            $suppress = false;

            if ($e instanceof NotImplementedException) {
                $level = 'info';
                $title = 'Not Implemented';
            }

            $this->notifyTelegram($level, $title, $suppress, $e);
        } catch (Throwable $e) {
        }
    }

    protected function notifyTelegram($level = 'error', $title = 'Exception', $suppress = false, Throwable $e)
    {
        Log::error($e);
        try {
            $helper = new TelegramErrorHelper();
            $helper->notify(new ErrorNotification($level, $title, $suppress, $e));
        } catch (Throwable $telegramE) {
            // Make sure Telegram exceptions don't stop regular exceptions being logged
            Log::error($telegramE);
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof FormValidationException) {
            if ($request->wantsJson()) {
                return \Response::json($e->getErrors(), 422);
            }
            \FlashNotification::error("Something wasn't right, please check the form for errors", $e->getErrors());
            return redirect()->back()->withInput();
        }

        if ($e instanceof ValidationException) {
            if ($request->wantsJson()) {
                return \Response::json($e->getMessage(), 422);
            }
            \FlashNotification::error($e->getMessage());
            return redirect()->back()->withInput();
        }

        if ($e instanceof NotImplementedException) {
            \FlashNotification::error("NotImplementedException: " . $e->getMessage());
            Log::warning($e);
            return redirect()->back()->withInput();
        }

        if ($e instanceof AuthenticationException) {
            if ($request->wantsJson()) {
                return \Response::json(['error' => $e->getMessage()], 403);
            }
            $userString = \Auth::guest() ? "A guest" : (\Auth::user() instanceof User ? \Auth::user()->name : \Auth::user()->getAuthIdentifier());
            Log::warning($userString . " tried to access something they weren't supposed to.");

            return \Response::view('errors.403', [], 403);
        }

        if ($e instanceof ModelNotFoundException) {
            $e = new HttpException(404, $e->getMessage());
        }

        if (config('app.debug') && $this->shouldReport($e) && !$request->wantsJson()) {
            return parent::render($request, $e);
        }

        if ($request->wantsJson()) {
            if ($this->isHttpException($e)) {
                /** @var HttpExceptionInterface $e */
                return \Response::json(['error' => $e->getMessage()], $e->getStatusCode());
            }
        }
        return parent::render($request, $e);
    }

    protected function invalid($request, IlluminateValidationException $exception)
    {
        \FlashNotification::error($exception->getMessage());

        return parent::invalid($request, $exception);
    }
}
