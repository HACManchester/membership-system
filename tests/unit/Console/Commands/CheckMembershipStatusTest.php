<?php

use BB\Entities\User;
use BB\Entities\SubscriptionCharge;
use Carbon\Carbon;
use Tests\TestCase;

class CheckMembershipStatusTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        
        $telegramHelper = $this->createStub(\BB\Helpers\TelegramHelper::class);
        $this->app->instance(\BB\Helpers\TelegramHelper::class, $telegramHelper);
    }

    public function testPaymentWarningUsersGetSuspended()
    {
        $user = User::factory()->create([
            'status' => 'payment-warning',
            'active' => true,
            'payment_method' => 'gocardless-variable',
            'subscription_expires' => Carbon::now()->subDays(2), // Grace period expired
        ]);

        $this->artisan('bb:check-memberships')->assertExitCode(0);

        $user->refresh();
        $this->assertEquals('suspended', $user->status);
        $this->assertFalse($user->active);
        $this->assertNotNull($user->suspended_at);
    }

    public function testPaymentWarningUsersWithinGracePeriodNotSuspended()
    {
        $user = User::factory()->create([
            'status' => 'payment-warning',
            'active' => true,
            'payment_method' => 'gocardless-variable',
            'subscription_expires' => Carbon::now()->addDays(3),
        ]);

        $this->artisan('bb:check-memberships')->assertExitCode(0);

        $user->refresh();
        $this->assertEquals('payment-warning', $user->status);
        $this->assertTrue($user->active);
    }

    public function testSuspendedUsersGetMarkedAsLeft()
    {
        $user = User::factory()->create([
            'status' => 'suspended',
            'active' => false,
            'suspended_at' => Carbon::now()->subDays(31), // Past 30 day grace period
            'payment_method' => 'gocardless-variable',
        ]);

        $this->artisan('bb:check-memberships')->assertExitCode(0);

        $user->refresh();
        $this->assertEquals('left', $user->status);
        $this->assertFalse($user->active);
    }

    public function testRecentlySuspendedUsersNotMarkedAsLeft()
    {
        $user = User::factory()->create([
            'status' => 'suspended',
            'active' => false,
            'suspended_at' => Carbon::now()->subDays(10),
            'payment_method' => 'gocardless-variable',
        ]);

        $this->artisan('bb:check-memberships')->assertExitCode(0);

        $user->refresh();
        $this->assertEquals('suspended', $user->status);
        $this->assertFalse($user->active);
    }

    public function testLeavingUsersGetMarkedAsLeftWhenSubscriptionExpires()
    {
        $user = User::factory()->create([
            'status' => 'leaving',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(1),
            'payment_method' => null, // Cancelled payment method
        ]);

        $this->artisan('bb:check-memberships')->assertExitCode(0);

        $user->refresh();
        $this->assertEquals('left', $user->status);
        $this->assertFalse($user->active);
    }

    public function testActiveUsersWithRecentPaymentStayActive()
    {
        $user = User::factory()->create([
            'status' => 'active',
            'active' => true,
            'payment_method' => 'gocardless-variable',
            'subscription_expires' => Carbon::now()->subDays(5), // Appears expired
        ]);

        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->subDays(10),
            'payment_date' => Carbon::now()->subDays(8),
            'amount' => 22,
            'status' => 'paid',
        ]);

        $this->artisan('bb:check-memberships')->assertExitCode(0);

        $user->refresh();
        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->active);
    }

    public function testMultipleUsersProcessedCorrectly()
    {
        $warningUser = User::factory()->create([
            'status' => 'payment-warning',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDays(1),
        ]);

        $suspendedUser = User::factory()->create([
            'status' => 'suspended',
            'active' => false,
            'suspended_at' => Carbon::now()->subDays(35),
        ]);

        $activeUser = User::factory()->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => Carbon::now()->addDays(10),
        ]);

        $this->artisan('bb:check-memberships')->assertExitCode(0);

        $warningUser->refresh();
        $this->assertEquals('suspended', $warningUser->status);

        $suspendedUser->refresh();
        $this->assertEquals('left', $suspendedUser->status);

        $activeUser->refresh();
        $this->assertEquals('active', $activeUser->status);
    }

    public function testHonoraryMembersNotAffected()
    {
        $user = User::factory()->create([
            'status' => 'honorary',
            'active' => true,
            'subscription_expires' => Carbon::now()->subMonths(6),
        ]);

        $this->artisan('bb:check-memberships')->assertExitCode(0);

        $user->refresh();
        $this->assertEquals('honorary', $user->status);
        $this->assertTrue($user->active);
    }
}