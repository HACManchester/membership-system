<?php

use BB\Entities\User;
use BB\Process\CheckExpiredActiveUsers;
use Carbon\Carbon;
use Tests\TestCase;

class CheckExpiredActiveUsersTest extends TestCase
{
    private $process;

    public function setUp(): void
    {
        parent::setUp();
        $this->process = new CheckExpiredActiveUsers();
    }

    public function testRunFlagsActiveUserWithLongLapsedExpiry()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(30),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('payment-warning', $user->status);
        $this->assertTrue($user->active); // Keeps access during the grace period
        $this->assertTrue($user->subscription_expires->isFuture()); // Grace period set
    }

    public function testRunLeavesRecentlyExpiredUserAloneWhileBacsClears()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(2),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('active', $user->status);
    }

    public function testRunLeavesPaidUpUserAlone()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => Carbon::now()->addDays(20),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('active', $user->status);
    }

    public function testRunFlagsEstablishedActiveUserWithNoExpiryDate()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => null,
        ]);
        $user->created_at = Carbon::now()->subDays(30);
        $user->save();

        $this->process->run();

        $user->refresh();
        $this->assertEquals('payment-warning', $user->status);
    }

    public function testRunLeavesNewUserWithNoExpiryDateAlone()
    {
        // A brand new member's first payment may still be working through GoCardless
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => null,
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('active', $user->status);
    }

    public function testRunIgnoresSpecialCaseAndNonActiveUsers()
    {
        $honoraryUser = factory(User::class)->create([
            'status' => 'honorary',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(30),
        ]);

        $giftedUser = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(30),
            'gift_expires' => Carbon::now()->addMonth(),
        ]);

        $suspendedUser = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'subscription_expires' => Carbon::now()->subDays(30),
        ]);

        $this->process->run();

        $this->assertEquals('honorary', $honoraryUser->refresh()->status);
        $this->assertEquals('active', $giftedUser->refresh()->status);
        $this->assertEquals('suspended', $suspendedUser->refresh()->status);
    }
}
