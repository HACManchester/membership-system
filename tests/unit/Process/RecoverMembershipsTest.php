<?php

use BB\Entities\User;
use BB\Entities\SubscriptionCharge;
use BB\Process\RecoverMemberships;
use Carbon\Carbon;
use Tests\TestCase;

class RecoverMembershipsTest extends TestCase
{
    private $process;

    public function setUp(): void
    {
        parent::setUp();
        $this->process = new RecoverMemberships();
    }

    /**
     * Test recovery for active users
     */
    public function testRecoverActiveUserWithExpiredMembership()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(5), // Expired 5 days ago
            'payment_method' => 'gocardless-variable',
        ]);

        // Create a valid payment that extends beyond expiry
        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->subDays(2),
            'status' => 'paid',
            'payment_date' => Carbon::now()->subDays(2),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->active);

        // Should be extended from payment date + 1 month
        $expectedExpiry = Carbon::now()->subDays(2)->addMonth();
        $this->assertTrue($user->subscription_expires->isSameDay($expectedExpiry));
    }

    public function testFixesTheSubscriptionDateOfActiveUser()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => Carbon::now()->addDays(15), // Still valid
            'payment_method' => 'gocardless-variable',
        ]);

        // Even with a newer payment, shouldn't extend if already valid
        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->subDays(1),
            'status' => 'paid',
            'payment_date' => Carbon::now()->subDays(1),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->subscription_expires->isSameDay(Carbon::now()->subDay()->addMonth()));
    }

    /**
     * Test recovery for payment-warning users
     */
    public function testRecoverPaymentWarningUser()
    {
        $user = factory(User::class)->create([
            'status' => 'payment-warning',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(1), // Grace period expired
            'payment_method' => 'gocardless-variable',
        ]);

        // Create payment that should recover membership
        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->subDays(3),
            'status' => 'paid',
            'payment_date' => Carbon::now()->subDays(3),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->active);
        $this->assertNotNull($user->subscription_expires);
    }

    /**
     * Test recovery for suspended users
     */
    public function testRecoverSuspendedUser()
    {
        $user = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'subscription_expires' => Carbon::now()->subDays(10),
            'suspended_at' => Carbon::now()->subDays(5),
            'payment_method' => 'gocardless-variable',
        ]);

        // Create recent payment
        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->subDays(1),
            'status' => 'paid',
            'payment_date' => Carbon::now()->subDays(1),
        ]);

        $this->process->run();

        $user->refresh();

        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->active);
        $this->assertNull($user->suspended_at);
    }

    public function testDoesNotRecoverUserWithOldPayments()
    {
        $user = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'subscription_expires' => Carbon::now()->subDays(5),
            'suspended_at' => Carbon::now()->subDays(3),
            'payment_method' => 'gocardless-variable',
        ]);

        // Payment is older than current expiry
        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->subDays(40),
            'status' => 'paid',
            'payment_date' => Carbon::now()->subDays(40),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('suspended', $user->status);
        $this->assertFalse($user->active);
    }

    public function testIgnoresLeavingUsers()
    {
        $user = factory(User::class)->create([
            'status' => 'leaving',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(1),
            'payment_method' => 'gocardless-variable',
        ]);

        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'status' => 'paid',
            'payment_date' => Carbon::now(),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('leaving', $user->status);
    }

    public function testIgnoresLeftUsers()
    {
        $user = factory(User::class)->create([
            'status' => 'left',
            'active' => false,
            'subscription_expires' => Carbon::now()->subDays(30),
            'payment_method' => 'gocardless-variable',
        ]);

        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'status' => 'paid',
            'payment_date' => Carbon::now(),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('left', $user->status);
        $this->assertFalse($user->active);
    }

    public function testHandlesUsersWithNoPayments()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(5),
            'payment_method' => 'gocardless-variable',
        ]);

        // No payments created

        $this->process->run();

        $user->refresh();

        // Should remain unchanged
        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->subscription_expires->isSameDay(Carbon::now()->subDays(5)));
    }

    public function testOnlyConsidersPaidSubscriptionCharges()
    {
        $user = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'subscription_expires' => Carbon::now()->subDays(5),
            'payment_method' => 'gocardless-variable',
        ]);

        // Create unpaid charges
        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'status' => 'pending',
            'payment_date' => null,
        ]);

        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->subDays(1),
            'status' => 'cancelled',
            'payment_date' => null,
        ]);

        $this->process->run();

        $user->refresh();

        // Should not recover with unpaid charges
        $this->assertEquals('suspended', $user->status);
        $this->assertFalse($user->active);
    }

    public function testRecoveryRespectsPaymentMethod()
    {
        $user = factory(User::class)->create([
            'status' => 'payment-warning',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(1),
            'payment_method' => 'cash', // Different payment method
        ]);

        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'status' => 'paid',
            'payment_date' => Carbon::now(),
        ]);

        $this->process->run();

        $user->refresh();

        // Should still recover regardless of payment method
        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->active);
    }
}
