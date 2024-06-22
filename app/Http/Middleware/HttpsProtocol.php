<?php namespace BB\Http\Middleware;
use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\App;

class HttpsProtocol
{
    /** @var Application */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle($request, Closure $next)
    {
        // TODO: Ideally this should be server config instead of app code.
        if (!$this->app->isLocal() && !$this->app->runningUnitTests() && !$request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request); 
    }
}

