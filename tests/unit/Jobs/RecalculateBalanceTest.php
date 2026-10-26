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

        RecalculateBalance::dispatchSync($user);

        $this->assertEquals(0, $user->fresh()->cash_balance);
    }

    public function testUserBalanceZeroWithPayments()
    {
        $user = factory(User::class)->create();

        Payment::factory()->count(2)->fromCash()->paid()->create([
            'reason' => 'balance',
            'user_id' => $user->id,
            'amount' => 20,
        ]);
        Payment::factory()->count(4)->fromBalance()->paid()->create([

            'reason' => 'not-balance',
            'user_id' => $user->id,
            'amount' => 10,
        ]);

        RecalculateBalance::dispatchSync($user);

        $this->assertEquals(0, $user->fresh()->cash_balance);
    }

    public function testUserWithPositiveBalance()
    {
        $user = factory(User::class)->create();

        Payment::factory()->count(2)->fromCash()->paid()->create([
            'reason' => 'balance',
            'user_id' => $user->id,
            'amount' => 20,
        ]);
        Payment::factory()->count(3)->fromBalance()->paid()->create([

            'reason' => 'not-balance',
            'user_id' => $user->id,
            'amount' => 10,
        ]);
        RecalculateBalance::dispatchSync($user);

        $this->assertEquals(1000, $user->fresh()->cash_balance);
    }

    public function testUserWithNegativeBalance()
    {
        $user = factory(User::class)->create();

        Payment::factory()->count(2)->fromCash()->paid()->create([
            'reason' => 'balance',
            'user_id' => $user->id,
            'amount' => 20,
        ]);
        Payment::factory()->count(6)->fromBalance()->paid()->create([

            'reason' => 'not-balance',
            'user_id' => $user->id,
            'amount' => 10,
        ]);
        RecalculateBalance::dispatchSync($user);

        $this->assertEquals(-2000, $user->fresh()->cash_balance);
    }

    public function testIncludesPendingPaymentsCancelledPayments()
    {
        $user = factory(User::class)->create();

        Payment::factory()->fromCash()->pending()->create([
            'reason' => 'balance',
            'user_id' => $user->id,
            'amount' => 20,
        ]);

        Payment::factory()->fromBalance()->paid()->create([
            'reason' => 'not-balance',
            'user_id' => $user->id,
            'amount' => 20,
        ]);

        RecalculateBalance::dispatchSync($user);

        $this->assertEquals(0, $user->fresh()->cash_balance);
    }

    public function testIgnoresCancelledPayments()
    {
        $user = factory(User::class)->create();

        Payment::factory()->fromCash()->cancelled()->create([
            'reason' => 'balance',
            'user_id' => $user->id,
            'amount' => 20,
        ]);

        Payment::factory()->fromBalance()->paid()->create([
            'reason' => 'not-balance',
            'user_id' => $user->id,
            'amount' => 20,
        ]);

        RecalculateBalance::dispatchSync($user);

        $this->assertEquals(-2000, $user->fresh()->cash_balance);
    }
}
