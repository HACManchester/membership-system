<?php namespace BB\Http\Middleware;

use Closure;

class UserSeen
{
    public function handle($request, Closure $next)
    {
        if(!\Auth::guest()) {
            \Auth::user()->markAsSeen();
        }

        return $next($request);
    }
}
