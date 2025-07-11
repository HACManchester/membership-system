<?php

use BB\Entities\User;
use BB\Entities\SubscriptionCharge;
use BB\Entities\Payment;
use BB\Process\CheckPaymentWarnings;
use BB\Repo\UserRepository;
use Carbon\Carbon;
use Tests\TestCase;

class CheckPaymentWarningsTest extends TestCase
{
    private $process;
    private $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = app(UserRepository::class);
        $this->process = new CheckPaymentWarnings($this->userRepository);
    }

    public function testRunSuspendsUserWhenGracePeriodExpired()
    {
        $user = factory(User::class)->create([
            'status' => 'payment-warning',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(1), // Grace period expired
            'suspended_at' => null,
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('suspended', $user->status);
        $this->assertFalse($user->active);
        $this->assertNotNull($user->suspended_at);
        $this->assertTrue($user->suspended_at->isToday());
    }

    public function testRunDoesNotSuspendUserWithinGracePeriod()
    {
        $user = factory(User::class)->create([
            'status' => 'payment-warning',
            'active' => true,
            'subscription_expires' => Carbon::now()->addDays(3), // Still within grace period
            'suspended_at' => null,
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('payment-warning', $user->status);
        $this->assertTrue($user->active);
        $this->assertNull($user->suspended_at);
    }

    public function testRunRecoversUserWithValidPayment()
    {
        $user = factory(User::class)->create([
            'status' => 'payment-warning',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(1), // Would normally be suspended
            'payment_method' => 'gocardless-variable',
        ]);

        // Create a valid payment that should extend membership
        $subscriptionCharge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->subDays(5),
            'status' => 'paid',
            'payment_date' => Carbon::now()->subDays(5),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->active);
        $this->assertNotNull($user->subscription_expires);
        
        // Should be extended based on payment date
        $expectedExpiry = Carbon::now()->subDays(5)->addMonth();
        $this->assertTrue($user->subscription_expires->isSameDay($expectedExpiry));
    }

    public function testRunIgnoresUsersNotInPaymentWarningStatus()
    {
        $activeUser = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(1),
        ]);

        $suspendedUser = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'subscription_expires' => Carbon::now()->subDays(1),
        ]);

        $this->process->run();

        $activeUser->refresh();
        $suspendedUser->refresh();

        // Should not change status of users not in payment-warning
        $this->assertEquals('active', $activeUser->status);
        $this->assertEquals('suspended', $suspendedUser->status);
    }

    public function testRunHandlesUserWithNoSubscriptionExpires()
    {
        $user = factory(User::class)->create([
            'status' => 'payment-warning',
            'active' => true,
            'subscription_expires' => null, // No expiry date
        ]);

        $this->process->run();

        $user->refresh();
        
        // Should suspend user with no expiry date
        $this->assertEquals('suspended', $user->status);
        $this->assertFalse($user->active);
    }

    public function testRunHandlesRecoveryWithNullSubscriptionExpires()
    {
        $user = factory(User::class)->create([
            'status' => 'payment-warning',
            'active' => true,
            'subscription_expires' => null,
            'payment_method' => 'gocardless-variable',
        ]);

        // Create valid payment
        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->subDays(3),
            'status' => 'paid',
            'payment_date' => Carbon::now()->subDays(3),
        ]);

        $this->process->run();

        $user->refresh();
        
        // Should recover user even with null subscription_expires
        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->active);
        $this->assertNotNull($user->subscription_expires);
    }

    public function testRunDoesNotRecoverWithOldPayment()
    {
        $user = factory(User::class)->create([
            'status' => 'payment-warning',
            'active' => true,
            'subscription_expires' => Carbon::now()->addDays(5), // Future date
            'payment_method' => 'gocardless-variable',
        ]);

        // Create payment that's older than current subscription_expires
        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->subDays(10),
            'status' => 'paid',
            'payment_date' => Carbon::now()->subDays(10),
        ]);

        $this->process->run();

        $user->refresh();
        
        // Should not recover since payment is older than current expiry
        $this->assertEquals('payment-warning', $user->status);
        $this->assertTrue($user->active);
    }
}