<?php

use BB\Entities\User;
use BB\Entities\SubscriptionCharge;
use Carbon\Carbon;
use Tests\TestCase;

class CreateTodaysSubChargesTest extends TestCase
{
    public function testDefaultOffsetCreatesChargesFor7DaysAhead()
    {
        // Use a fixed date to avoid month boundary issues
        // payment_day > 28 gets converted to 1 by User mutator
        $targetDay = 15;
        
        $user1 = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => $targetDay,
            'monthly_subscription' => 22,
        ]);

        $user2 = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => $targetDay,
            'monthly_subscription' => 17,
        ]);

        // Mock the date so the command thinks it's 7 days before payment day
        Carbon::setTestNow(Carbon::now()->setDay($targetDay)->subDays(7));
        
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
        
        Carbon::setTestNow(); // Reset
    }

    public function testCustomOffsetCreatesChargesForCorrectDate()
    {
        $customOffset = 14;
        $paymentDay = 10; // Use a day that exists in all months
        
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => $paymentDay,
            'monthly_subscription' => 22,
        ]);

        // Mock the date so it's customOffset days before the payment day
        Carbon::setTestNow(Carbon::now()->setDay($paymentDay)->subDays($customOffset));
        
        $this->artisan('bb:create-todays-sub-charges', [
            'dayOffset' => $customOffset
        ])->assertExitCode(0);

        $targetDate = Carbon::now()->addDays($customOffset);
        
        $charge = SubscriptionCharge::where('user_id', $user->id)
            ->whereDate('charge_date', $targetDate)
            ->first();
            
        $this->assertNotNull($charge);
        $this->assertEquals(22, $charge->amount);
        
        Carbon::setTestNow(); // Reset
    }

    public function testZeroOffsetCreatesChargesForToday()
    {
        $paymentDay = 20; // Use a day that exists in all months
        
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => $paymentDay,
            'monthly_subscription' => 27,
        ]);

        // Mock the date to be on the payment day
        Carbon::setTestNow(Carbon::now()->setDay($paymentDay));
        
        $this->artisan('bb:create-todays-sub-charges', [
            'dayOffset' => 0
        ])->assertExitCode(0);

        $charge = SubscriptionCharge::where('user_id', $user->id)
            ->whereDate('charge_date', Carbon::now())
            ->first();
            
        $this->assertNotNull($charge);
        $this->assertEquals(27, $charge->amount);
        
        Carbon::setTestNow(); // Reset
    }

    public function testNegativeOffsetCreatesChargesForPastDate()
    {
        $negativeOffset = -3;
        $paymentDay = 25; // Use a day that exists in all months
        
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => $paymentDay,
            'monthly_subscription' => 22,
        ]);

        // Mock the date so it's 3 days after the payment day
        Carbon::setTestNow(Carbon::now()->setDay($paymentDay)->addDays(3));
        
        $this->artisan('bb:create-todays-sub-charges', [
            'dayOffset' => $negativeOffset
        ])->assertExitCode(0);

        $targetDate = Carbon::now()->addDays($negativeOffset);
        
        $charge = SubscriptionCharge::where('user_id', $user->id)
            ->whereDate('charge_date', $targetDate)
            ->first();
            
        $this->assertNotNull($charge);
        
        Carbon::setTestNow(); // Reset
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
        $paymentDay = 15; // Use a day that exists in all months
        
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => $paymentDay,
            'monthly_subscription' => 22,
        ]);

        // Mock the date so it's 7 days before the payment day
        Carbon::setTestNow(Carbon::now()->setDay($paymentDay)->subDays(7));
        
        $this->artisan('bb:create-todays-sub-charges')->assertExitCode(0);
        $this->artisan('bb:create-todays-sub-charges')->assertExitCode(0);

        $targetDate = Carbon::now()->addDays(7);
        
        $charges = SubscriptionCharge::where('user_id', $user->id)
            ->whereDate('charge_date', $targetDate)
            ->get();
            
        $this->assertEquals(1, $charges->count(), 'Expected 1 charge but found ' . $charges->count());
        
        Carbon::setTestNow(); // Reset
    }

    public function testChargesCreatedWithCorrectAmount()
    {
        $paymentDay = 15; // Use a day that exists in all months
        
        $amounts = [
            17 => 'Low income',
            22 => 'Standard',
            27 => 'Supporter'
        ];

        // Mock the date so it's 7 days before the payment day
        Carbon::setTestNow(Carbon::now()->setDay($paymentDay)->subDays(7));
        
        foreach ($amounts as $amount => $type) {
            $user = factory(User::class)->create([
                'status' => 'active',
                'payment_method' => 'gocardless-variable',
                'payment_day' => $paymentDay,
                'monthly_subscription' => $amount,
            ]);

            $this->artisan('bb:create-todays-sub-charges')->assertExitCode(0);

            $targetDate = Carbon::now()->addDays(7);
            
            $charge = SubscriptionCharge::where('user_id', $user->id)
                ->whereDate('charge_date', $targetDate)
                ->first();
                
            $this->assertNotNull($charge, "Charge not created for $type user");
            $this->assertEquals($amount, $charge->amount, "Incorrect amount for $type user");
        }
        
        Carbon::setTestNow(); // Reset
    }
}