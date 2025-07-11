<?php

use BB\Entities\User;
use BB\Entities\SubscriptionCharge;
use BB\Process\CheckMemberships;
use BB\Services\MemberSubscriptionCharges;
use BB\Repo\UserRepository;
use Carbon\Carbon;
use Tests\TestCase;

class CheckMembershipsTest extends TestCase
{
    private $process;
    private $memberSubscriptionCharges;

    public function setUp(): void
    {
        parent::setUp();
        $this->memberSubscriptionCharges = app(MemberSubscriptionCharges::class);
        $this->process = new CheckMemberships($this->memberSubscriptionCharges);
    }

    public function testRunMarksUserAsLeftAfter30DaysSuspension()
    {
        $user = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'suspended_at' => Carbon::now()->subDays(31),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('left', $user->status);
        $this->assertFalse($user->active);
    }

    public function testRunDoesNotMarkUserAsLeftBefore30Days()
    {
        $user = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'suspended_at' => Carbon::now()->subDays(29),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('suspended', $user->status);
        $this->assertFalse($user->active);
    }

    public function testRunMarksUserAsLeftExactly30DaysAfterSuspension()
    {
        $user = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'suspended_at' => Carbon::now()->subDays(30),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('left', $user->status);
        $this->assertFalse($user->active);
    }

    public function testRunIgnoresSuspendedUserWithNoSuspendedAt()
    {
        $user = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'suspended_at' => null, // No suspension date
        ]);

        $this->process->run();

        $user->refresh();
        // Should remain suspended since we don't know when they were suspended
        $this->assertEquals('suspended', $user->status);
        $this->assertFalse($user->active);
    }

    public function testRunIgnoresUsersNotInSuspendedStatus()
    {
        $activeUser = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'suspended_at' => Carbon::now()->subDays(31),
        ]);

        $warningUser = factory(User::class)->create([
            'status' => 'payment-warning',
            'active' => true,
            'suspended_at' => Carbon::now()->subDays(31),
        ]);

        $leftUser = factory(User::class)->create([
            'status' => 'left',
            'active' => false,
            'suspended_at' => Carbon::now()->subDays(31),
        ]);

        $this->process->run();

        $activeUser->refresh();
        $warningUser->refresh();
        $leftUser->refresh();

        $this->assertEquals('active', $activeUser->status);
        $this->assertEquals('payment-warning', $warningUser->status);
        $this->assertEquals('left', $leftUser->status);
    }

    public function testRunRecoversMembershipWithValidPayment()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(15),
            'payment_method' => 'gocardless-variable',
        ]);

        // Create valid payment that should extend membership
        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->subDays(5),
            'status' => 'paid',
            'payment_date' => Carbon::now()->subDays(5),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->active);
        
        // Should extend membership based on payment date
        $expectedExpiry = Carbon::now()->subDays(5)->addMonth();
        $this->assertTrue($user->subscription_expires->isSameDay($expectedExpiry));
    }

    public function testRunDoesNotRecoverWithOldPayment()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => Carbon::now()->addDays(10), // Future date
            'payment_method' => 'gocardless-variable',
        ]);

        // Create payment that's older than current subscription_expires
        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->subDays(30),
            'status' => 'paid',
            'payment_date' => Carbon::now()->subDays(30),
        ]);

        $this->process->run();

        $user->refresh();
        
        // Should not extend since payment is older than current expiry
        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->active);
        $this->assertTrue($user->subscription_expires->isSameDay(Carbon::now()->addDays(10)));
    }
}