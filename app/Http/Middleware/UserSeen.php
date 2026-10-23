<?php namespace BB\Http\Middleware;

use BB\Support\CurrentUser;
use Closure;

class UserSeen
{
    public function handle($request, Closure $next)
    {
        CurrentUser::find()?->markAsSeen();

        return $next($request);
    }
}
