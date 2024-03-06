<?php

namespace BB\Exceptions;

use BB\Helpers\TelegramErrorHelper;
use BB\Helpers\TelegramHelper;
use BB\Notifications\ErrorNotification;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException as IlluminateValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        NotFoundHttpException::class,
        ModelNotFoundException::class,
        MethodNotAllowedHttpException::class,
        FormValidationException::class,
        AuthenticationException::class,         //These are logged separately below
        AuthorizationException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        IlluminateValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
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

    protected function telegramException(Exception $e)
    {
        try {
            $level = 'error';
            $title = 'Error';
            $suppress = false;
            $ignore = false;

            if ($e instanceof NotImplementedException) {
                $level = 'info';
                $title = 'Not Implemented';
            }

            if (!$ignore) $this->notifyTelegram($level, $title, $suppress, $e);
        } catch (Exception $e) {
        }
    }

    protected function notifyTelegram($level = 'error', $title = 'Exception', $suppress = false, Exception $e)
    {
        \Log::error($e);
        try {
            $helper = new TelegramErrorHelper();
            $helper->notify(new ErrorNotification($level, $title, $suppress, $e));
        } catch (Exception $telegramE) {
            // Make sure Telegram exceptions don't stop regular exceptions being logged
            \Log::error($telegramE);
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof FormValidationException) {
            if ($request->wantsJson()) {
                return \Response::json($e->getErrors(), 422);
            }
            \FlashNotification::error("Something wasn't right, please check the form for errors", $e->getErrors());
            return redirect()->back()->withInput();
        }

        if ($e instanceof ValidationException || $e instanceof IlluminateValidationException) {
            if ($request->wantsJson()) {
                return \Response::json($e->getMessage(), 422);
            }
            \FlashNotification::error($e->getMessage());
            return redirect()->back()->withInput();
        }

        if ($e instanceof NotImplementedException) {
            \FlashNotification::error("NotImplementedException: " . $e->getMessage());
            \Log::warning($e);
            return redirect()->back()->withInput();
        }

        if ($e instanceof AuthenticationException) {
            if ($request->wantsJson()) {
                return \Response::json(['error' => $e->getMessage()], 403);
            }
            $userString = \Auth::guest() ? "A guest" : \Auth::user()->name;
            \Log::warning($userString . " tried to access something they weren't supposed to.");

            return \Response::view('errors.403', [], 403);
        }

        if ($e instanceof ModelNotFoundException) {
            $e = new HttpException(404, $e->getMessage());
        }

        if (config('app.debug') && $this->shouldReport($e) && !$request->wantsJson()) {
            return $this->renderExceptionWithWhoops($e);
        }

        if ($request->wantsJson()) {
            if ($this->isHttpException($e)) {
                return \Response::json(['error' => $e->getMessage()], $e->getStatusCode());
            }
        }
        return parent::render($request, $e);
    }

    /**
     * Render an exception using Whoops.
     *
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    protected function renderExceptionWithWhoops(Exception $e)
    {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());

        return new \Illuminate\Http\Response($whoops->handleException($e), $e->getCode());
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
