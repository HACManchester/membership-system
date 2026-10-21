<?php

namespace BB\Http\Controllers;

use BB\Support\CurrentUser;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $user = CurrentUser::find();

        if ($user) {
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
