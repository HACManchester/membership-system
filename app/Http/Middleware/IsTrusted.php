<?php namespace BB\Http\Middleware;

use BB\Exceptions\AuthenticationException;
use Closure;

class IsTrusted
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     * @throws AuthenticationException
     */
    public function handle($request, Closure $next)
    {
        if (\Auth::guest()) {
            //Guests should be redirected to the login page as we make some links visible
            if (\Request::ajax()) {
                return \Response::make('Unauthorized', 401);
            } else {
                return \Redirect::guest('login');
            }

        } elseif (\Auth::user()->isBanned()) {
            throw new AuthenticationException();
            
        } elseif(!\Auth::user()->isAdmin() && !\Auth::user()->trusted) {
            throw new AuthenticationException();
        }

        return $next($request);
    }

}
