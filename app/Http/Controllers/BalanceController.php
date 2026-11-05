<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Http\Resources\BalancePaymentResource;
use BB\Services\Credit;
use Inertia\Inertia;
use Inertia\Response;

class BalanceController extends Controller
{
    public function __construct(private Credit $bbCredit)
    {
    }

    public function index(string $userId): Response
    {
        $user = User::findOrFail($userId);
        $this->authorize('manage', $user);
        $this->bbCredit->setUserId($user->id);

        // cash_balance is in pence
        $balance = abs($user->cash_balance) / 100;

        return Inertia::render('Balance/Index', [
            'balance' => [
                'formatted' => ($user->cash_balance < 0 ? '-' : '') . '£' . number_format($balance, 2),
                'negative' => $user->cash_balance < 0,
            ],
            'payments' => $this->bbCredit->getBalancePaymentsPaginated()
                ->through(fn ($payment) => (new BalancePaymentResource($payment))->resolve()),
        ]);
    }
}
