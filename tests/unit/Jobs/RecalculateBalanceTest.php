<?php

use BB\Entities\Payment;
use BB\Entities\User;
use BB\Jobs\RecalculateBalance;
use Tests\TestCase;

class RecalculateBalanceTest extends TestCase
{
    public function testUserHasNoPayments()
    {
        $user = factory(User::class)->create();

        RecalculateBalance::dispatchNow($user);

        $this->assertEquals(0, $user->fresh()->cash_balance);
    }

    public function testUserBalanceZeroWithPayments()
    {
        $user = factory(User::class)->create();

        factory(Payment::class, 2)->states(['fromCash', 'paid'])->create([
            'reason' => 'balance',
            'user_id' => $user->id,
            'amount' => 20,
        ]);
        factory(Payment::class, 4)->states(['fromBalance', 'paid'])->create([

            'reason' => 'not-balance',
            'user_id' => $user->id,
            'amount' => 10,
        ]);

        RecalculateBalance::dispatchNow($user);

        $this->assertEquals(0, $user->fresh()->cash_balance);
    }

    public function testUserWithPositiveBalance()
    {
        $user = factory(User::class)->create();

        factory(Payment::class, 2)->states(['fromCash', 'paid'])->create([
            'reason' => 'balance',
            'user_id' => $user->id,
            'amount' => 20,
        ]);
        factory(Payment::class, 3)->states(['fromBalance', 'paid'])->create([

            'reason' => 'not-balance',
            'user_id' => $user->id,
            'amount' => 10,
        ]);
        RecalculateBalance::dispatchNow($user);

        $this->assertEquals(1000, $user->fresh()->cash_balance);
    }

    public function testUserWithNegativeBalance()
    {
        $user = factory(User::class)->create();

        factory(Payment::class, 2)->states(['fromCash', 'paid'])->create([
            'reason' => 'balance',
            'user_id' => $user->id,
            'amount' => 20,
        ]);
        factory(Payment::class, 6)->states(['fromBalance', 'paid'])->create([

            'reason' => 'not-balance',
            'user_id' => $user->id,
            'amount' => 10,
        ]);
        RecalculateBalance::dispatchNow($user);

        $this->assertEquals(-2000, $user->fresh()->cash_balance);
    }

    public function testIncludesPendingPaymentsCancelledPayments()
    {
        $user = factory(User::class)->create();

        factory(Payment::class)->states(['fromCash', 'pending'])->create([
            'reason' => 'balance',
            'user_id' => $user->id,
            'amount' => 20,
        ]);

        factory(Payment::class)->states(['fromBalance', 'paid'])->create([
            'reason' => 'not-balance',
            'user_id' => $user->id,
            'amount' => 20,
        ]);

        RecalculateBalance::dispatchNow($user);

        $this->assertEquals(0, $user->fresh()->cash_balance);
    }

    public function testIgnoresCancelledPayments()
    {
        $user = factory(User::class)->create();

        factory(Payment::class)->states(['fromCash', 'cancelled'])->create([
            'reason' => 'balance',
            'user_id' => $user->id,
            'amount' => 20,
        ]);

        factory(Payment::class)->states(['fromBalance', 'paid'])->create([
            'reason' => 'not-balance',
            'user_id' => $user->id,
            'amount' => 20,
        ]);

        RecalculateBalance::dispatchNow($user);

        $this->assertEquals(-2000, $user->fresh()->cash_balance);
    }
}
