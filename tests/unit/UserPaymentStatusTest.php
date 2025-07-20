<?php

use BB\Entities\User;
use Carbon\Carbon;
use Tests\TestCase;

class UserPaymentStatusTest extends TestCase
{
    public function testSetPaymentWarningWithDefaultGracePeriod()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'subscription_expires' => Carbon::now()->addDays(5),
        ]);

        $user->setPaymentWarning();

        $this->assertEquals('payment-warning', $user->status);
        $this->assertTrue($user->active);
        $this->assertNotNull($user->subscription_expires);
        
        $expectedExpiry = Carbon::now()->addDays(10);
        $this->assertTrue($user->subscription_expires->isSameDay($expectedExpiry));
    }

    public function testSetPaymentWarningWithCustomGracePeriod()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
        ]);

        $user->setPaymentWarning(100);

        $this->assertEquals('payment-warning', $user->status);
        $this->assertTrue($user->active);
        
        $expectedExpiry = Carbon::now()->addDays(100);
        $this->assertTrue($user->subscription_expires->isSameDay($expectedExpiry));
    }

    public function testSetSuspendedRecordsTimestamp()
    {
        $user = factory(User::class)->create([
            'status' => 'payment-warning',
            'active' => true,
            'suspended_at' => null,
        ]);

        $user->setSuspended();

        $this->assertEquals('suspended', $user->status);
        $this->assertFalse($user->active);
        $this->assertNotNull($user->suspended_at);
        
        $this->assertTrue($user->suspended_at->isToday());
    }

    public function testSetSuspendedUpdatesExistingSuspendedAt()
    {
        $oldSuspendedAt = Carbon::now()->subDays(5);
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'suspended_at' => $oldSuspendedAt,
        ]);

        $user->setSuspended();

        $this->assertEquals('suspended', $user->status);
        $this->assertFalse($user->active);

        $this->assertTrue($user->suspended_at->isToday());
        $this->assertNotEquals($oldSuspendedAt->toDateTimeString(), $user->suspended_at->toDateTimeString());
    }
}