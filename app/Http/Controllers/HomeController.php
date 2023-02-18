<?php

namespace BB\Http\Controllers;

use BB\Entities\User;

class HomeController extends Controller
{
    public function index()
    {
        $user = \Auth::user();

        if ($user instanceof User) {
            return \Redirect::route('account.show', [$user->id]);
        }

        \View::share('body_class', 'home');
        return \View::make('home');
    }
}
