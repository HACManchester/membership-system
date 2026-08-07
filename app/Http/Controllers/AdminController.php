<?php

namespace BB\Http\Controllers;

use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Index', [
            'urls' => [
                'accounts' => route('account.index', [], false),
                'recentMembers' => route('account.index', [], false) . '?sortBy=seen_at&direction=desc&limit=20',
                'logs' => route('logs', [], false),
                'roles' => route('roles.index', [], false),
                'payments' => route('payments.index', [], false),
                'subCharges' => route('payments.sub-charges', [], false),
            ],
        ]);
    }
}
