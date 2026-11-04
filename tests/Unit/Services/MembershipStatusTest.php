<?php

namespace Tests\Unit\Services;

use BB\Entities\User;
use BB\Services\MembershipStatus;
use BB\Support\MemberStatus;
use Carbon\Carbon;
use Tests\TestCase;

class MembershipStatusTest extends TestCase
{
    public function testSetPaymentWarningWithDefaultGracePeriod()
    {
        $user = User::factory()->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => Carbon::now()->addDays(5),
        ]);

        app(MembershipStatus::class)->setPaymentWarning($user);

        $this->assertSame(MemberStatus::PaymentWarning, $user->status);
        $this->assertTrue($user->active);
        $this->assertNotNull($user->subscription_expires);
        
        $expectedExpiry = Carbon::now()->addDays(10);
        $this->assertTrue($user->subscription_expires->isSameDay($expectedExpiry));
    }

    public function testSetPaymentWarningWithCustomGracePeriod()
    {
        $user = User::factory()->create([
            'status' => 'active',
            'active' => true,
        ]);

        app(MembershipStatus::class)->setPaymentWarning($user, 100);

        $this->assertSame(MemberStatus::PaymentWarning, $user->status);
        $this->assertTrue($user->active);
        
        $expectedExpiry = Carbon::now()->addDays(100);
        $this->assertTrue($user->subscription_expires->isSameDay($expectedExpiry));
    }

    public function testSetSuspendedRecordsTimestamp()
    {
        $user = User::factory()->create([
            'status' => 'payment-warning',
            'active' => true,
            'suspended_at' => null,
        ]);

        app(MembershipStatus::class)->suspend($user);

        $this->assertSame(MemberStatus::Suspended, $user->status);
        $this->assertFalse($user->active);
        $this->assertNotNull($user->suspended_at);
        
        $this->assertTrue($user->suspended_at->isToday());
    }

    public function testSetSuspendedUpdatesExistingSuspendedAt()
    {
        $oldSuspendedAt = Carbon::now()->subDays(5);
        $user = User::factory()->create([
            'status' => 'active',
            'active' => true,
            'suspended_at' => $oldSuspendedAt,
        ]);

        app(MembershipStatus::class)->suspend($user);

        $this->assertSame(MemberStatus::Suspended, $user->status);
        $this->assertFalse($user->active);

        $this->assertTrue($user->suspended_at->isToday());
        $this->assertNotEquals($oldSuspendedAt->toDateTimeString(), $user->suspended_at->toDateTimeString());
    }
}
