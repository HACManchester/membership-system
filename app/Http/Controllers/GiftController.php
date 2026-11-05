<?php

namespace BB\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class GiftController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Gift', [
            'urls' => [
                'register' => route('register', [], false),
            ],
        ]);
    }
}
