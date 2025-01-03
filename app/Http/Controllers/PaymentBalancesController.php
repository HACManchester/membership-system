<?php

namespace BB\Http\Controllers;

use BB\Entities\User;

class PaymentBalancesController extends Controller
{
    public function index()
    {
        $activeUsersInCreditQty =  User::where('active', true)->where('cash_balance', '>', 0)->count();
        $activeUsersInCreditSum =  User::where('active', true)->where('cash_balance', '>', 0)->sum('cash_balance') / 100;

        $activeUsersInDebtQty =  User::where('active', true)->where('cash_balance', '<', 0)->count();
        $activeUsersInDebtSum =  User::where('active', true)->where('cash_balance', '<', 0)->sum('cash_balance') / 100;

        $inactiveUsersInCreditQty = User::where('active', false)->where('cash_balance', '>', 0)->count();
        $inactiveUsersInCreditSum = User::where('active', false)->where('cash_balance', '>', 0)->sum('cash_balance') / 100;
        $inactiveUsersInDebtQty = User::where('active', false)->where('cash_balance', '<', 0)->count();
        $inactiveUsersInDebtSum = User::where('active', false)->where('cash_balance', '<', 0)->sum('cash_balance') / 100;

        $activeUsers = User::where('active', true)->where('cash_balance', '!=', 0)->orderBy('cash_balance', 'desc')->get();
        $inactiveUsers = User::where('active', false)->where('cash_balance', '!=', 0)->orderBy('cash_balance', 'desc')->get();

        return \View::make('payment_balances.index')->with([
            'activeUsersInCreditQty' => $activeUsersInCreditQty,
            'activeUsersInCreditSum' => $activeUsersInCreditSum,
            'activeUsersInDebtQty' => $activeUsersInDebtQty,
            'activeUsersInDebtSum' => $activeUsersInDebtSum,
            'inactiveUsersInCreditQty' => $inactiveUsersInCreditQty,
            'inactiveUsersInCreditSum' => $inactiveUsersInCreditSum,
            'inactiveUsersInDebtQty' => $inactiveUsersInDebtQty,
            'inactiveUsersInDebtSum' => $inactiveUsersInDebtSum,
            'activeUsers' => $activeUsers,
            'inactiveUsers' => $inactiveUsers,
        ]);
    }
}
