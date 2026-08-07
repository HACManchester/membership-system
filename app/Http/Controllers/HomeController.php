<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $user = \Auth::user();

        if ($user instanceof User) {
            return \Redirect::route('account.show', [$user->id]);
        }

        return Inertia::render('Home', [
            'urls' => [
                'register' => route('register', [], false),
                'gift' => route('gift', [], false),
                'login' => route('login', [], false),
            ],
        ]);
    }
}
