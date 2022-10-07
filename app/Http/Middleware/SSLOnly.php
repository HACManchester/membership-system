<?php namespace BB\Http\Middleware;

use Closure;
use Illuminate\Foundation\Application;

class SSLOnly
{
    /** @var Application */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Verify the incoming request is via an ssl connection unless its on an approved url
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // TODO: Ideally this should be server config instead of app code.
        if(!$this->app->isLocal() && ! $request->isSecure() && env('FORCE_SECURE', 'true')) {
            if ((strpos($request->path(), 'access-control/') !== 0) && ($request->path() !== 'acs') && ($request->path() !== 'acs/spark')) {
                return redirect()->secure($request->path());
            }
        }

        return $next($request);
    }
}
