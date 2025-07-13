<?php

use BB\Entities\User;
use BB\Entities\SubscriptionCharge;
use Carbon\Carbon;
use Tests\TestCase;

class CreateTodaysSubChargesTest extends TestCase
{
    public function testDefaultOffsetCreatesChargesFor7DaysAhead()
    {
        $user1 = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => Carbon::now()->addDays(7)->day,
            'monthly_subscription' => 22,
        ]);

        $user2 = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => Carbon::now()->addDays(7)->day,
            'monthly_subscription' => 17,
        ]);

        $this->artisan('bb:create-todays-sub-charges')->assertExitCode(0);

        $expectedDate = Carbon::now()->addDays(7);
        
        $charge1 = SubscriptionCharge::where('user_id', $user1->id)
            ->whereDate('charge_date', $expectedDate)
            ->first();
            
        $this->assertNotNull($charge1);
        $this->assertEquals(22, $charge1->amount);
        $this->assertEquals('pending', $charge1->status);

        $charge2 = SubscriptionCharge::where('user_id', $user2->id)
            ->whereDate('charge_date', $expectedDate)
            ->first();
            
        $this->assertNotNull($charge2);
        $this->assertEquals(17, $charge2->amount);
        $this->assertEquals('pending', $charge2->status);
    }

    public function testCustomOffsetCreatesChargesForCorrectDate()
    {
        $customOffset = 14;
        $targetDate = Carbon::now()->addDays($customOffset);
        
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => $targetDate->day,
            'monthly_subscription' => 22,
        ]);

        $this->artisan('bb:create-todays-sub-charges', [
            'dayOffset' => $customOffset
        ])->assertExitCode(0);

        $charge = SubscriptionCharge::where('user_id', $user->id)
            ->whereDate('charge_date', $targetDate)
            ->first();
            
        $this->assertNotNull($charge);
        $this->assertEquals(22, $charge->amount);
    }

    public function testZeroOffsetCreatesChargesForToday()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => Carbon::now()->day,
            'monthly_subscription' => 27,
        ]);

        $this->artisan('bb:create-todays-sub-charges', [
            'dayOffset' => 0
        ])->assertExitCode(0);

        $charge = SubscriptionCharge::where('user_id', $user->id)
            ->whereDate('charge_date', Carbon::now())
            ->first();
            
        $this->assertNotNull($charge);
        $this->assertEquals(27, $charge->amount);
    }

    public function testNegativeOffsetCreatesChargesForPastDate()
    {
        $negativeOffset = -3;
        $targetDate = Carbon::now()->addDays($negativeOffset);
        
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => $targetDate->day,
            'monthly_subscription' => 22,
        ]);

        $this->artisan('bb:create-todays-sub-charges', [
            'dayOffset' => $negativeOffset
        ])->assertExitCode(0);

        $charge = SubscriptionCharge::where('user_id', $user->id)
            ->whereDate('charge_date', $targetDate)
            ->first();
            
        $this->assertNotNull($charge);
    }

    public function testNoChargesCreatedForInactiveUsers()
    {
        $targetDate = Carbon::now()->addDays(7);
        
        $suspendedUser = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'payment_day' => $targetDate->day,
            'monthly_subscription' => 22,
        ]);

        $leftUser = factory(User::class)->create([
            'status' => 'left',
            'active' => false,
            'payment_day' => $targetDate->day,
            'monthly_subscription' => 22,
        ]);

        $this->artisan('bb:create-todays-sub-charges')->assertExitCode(0);

        $suspendedCharges = SubscriptionCharge::where('user_id', $suspendedUser->id)
            ->whereDate('charge_date', $targetDate)
            ->count();
            
        $this->assertEquals(0, $suspendedCharges);

        $leftCharges = SubscriptionCharge::where('user_id', $leftUser->id)
            ->whereDate('charge_date', $targetDate)
            ->count();
            
        $this->assertEquals(0, $leftCharges);
    }

    public function testDuplicateChargesNotCreated()
    {
        $targetDate = Carbon::now()->addDays(7);
        
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => $targetDate->day,
            'monthly_subscription' => 22,
        ]);

        $this->artisan('bb:create-todays-sub-charges')->assertExitCode(0);
        $this->artisan('bb:create-todays-sub-charges')->assertExitCode(0);

        $charges = SubscriptionCharge::where('user_id', $user->id)
            ->whereDate('charge_date', $targetDate)
            ->get();
            
        $allCharges = SubscriptionCharge::where('user_id', $user->id)->get();
        $chargeDetails = $allCharges->map(function($c) { return $c->charge_date->format('Y-m-d H:i:s'); })->implode(', ');
        $this->assertEquals(1, $charges->count(), 'Expected 1 charge but found ' . $charges->count() . '. Target date: ' . $targetDate->format('Y-m-d') . '. All charges: ' . $chargeDetails);
    }

    public function testChargesCreatedWithCorrectAmount()
    {
        $targetDate = Carbon::now()->addDays(7);
        
        $amounts = [
            17 => 'Low income',
            22 => 'Standard',
            27 => 'Supporter'
        ];

        foreach ($amounts as $amount => $type) {
            $user = factory(User::class)->create([
                'status' => 'active',
                'payment_method' => 'gocardless-variable',
                'payment_day' => $targetDate->day,
                'monthly_subscription' => $amount,
            ]);

            $this->artisan('bb:create-todays-sub-charges')->assertExitCode(0);

            $charge = SubscriptionCharge::where('user_id', $user->id)
                ->whereDate('charge_date', $targetDate)
                ->first();
                
            $this->assertNotNull($charge, "Charge not created for $type user");
            $this->assertEquals($amount, $charge->amount, "Incorrect amount for $type user");
        }
    }
}