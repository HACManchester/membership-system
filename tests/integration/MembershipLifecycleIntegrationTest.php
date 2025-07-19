<?php

use BB\Entities\User;
use BB\Process\CheckPaymentWarnings;
use BB\Process\CheckSuspendedUsers;
use BB\Process\CheckLeavingUsers;
use BB\Process\RecoverMemberships;
use BB\Services\MemberSubscriptionCharges;
use BB\Repo\UserRepository;
use BB\Repo\SubscriptionChargeRepository;
use BB\Handlers\SubChargeEventHandler;
use Carbon\Carbon;
use Tests\TestCase;

class MembershipLifecycleIntegrationTest extends TestCase
{
    private $userRepository;
    private $subscriptionChargeRepository;
    private $subChargeEventHandler;
    private $checkPaymentWarnings;
    private $checkSuspendedUsers;
    private $checkLeavingUsers;
    private $recoverMemberships;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = app(UserRepository::class);
        $this->subscriptionChargeRepository = app(SubscriptionChargeRepository::class);
        $this->subChargeEventHandler = new SubChargeEventHandler($this->userRepository);
        $this->checkPaymentWarnings = new CheckPaymentWarnings();
        $this->checkSuspendedUsers = new CheckSuspendedUsers($this->userRepository);
        $this->checkLeavingUsers = new CheckLeavingUsers($this->userRepository);
        $this->recoverMemberships = new RecoverMemberships();
    }

    public function testCompleteNewMemberJourney()
    {
        // Step 1: New member registration
        $user = factory(User::class)->create([
            'status' => 'setting-up',
            'active' => false,
            'payment_method' => null,
            'subscription_expires' => null,
            'monthly_subscription' => 2200,
        ]);

        $this->assertEquals('setting-up', $user->status);
        $this->assertFalse($user->active);

        // Step 2: Member sets up payment method and becomes active
        $user->status = 'active';
        $user->active = true;
        $user->payment_method = 'gocardless-variable';
        $user->subscription_expires = Carbon::now()->addMonth();
        $user->payment_day = 15;
        $user->save();

        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->active);

        // Step 3: Simulate 6 months of successful payments
        $currentDate = Carbon::now();
        for ($month = 0; $month < 6; $month++) {
            $paymentDate = $currentDate->copy()->addMonths($month);
            Carbon::setTestNow($paymentDate);

            // Create successful subscription charge
            $charge = $this->subscriptionChargeRepository->createCharge(
                $user->id,
                $paymentDate,
                2200,
                'paid'
            );
            $charge->payment_date = $paymentDate;
            $charge->save();

            // Extend membership
            $user->extendMembership($user->payment_method, $paymentDate->copy()->addMonth());
        }

        $user->refresh();
        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->active);

        // Step 4: First payment failure
        $failureDate = $currentDate->copy()->addMonths(6);
        Carbon::setTestNow($failureDate);

        $failedCharge = $this->subscriptionChargeRepository->createCharge(
            $user->id,
            $failureDate,
            2200,
            'due'
        );

        $this->subChargeEventHandler->onPaymentFailure(
            $failedCharge->id,
            $user->id,
            $failureDate,
            2200
        );

        $user->refresh();
        $this->assertEquals('payment-warning', $user->status);
        $this->assertTrue($user->active); // Should still have access

        // Step 5: Member recovers by using payment retry (simulates PaymentModule flow)
        $recoveryDate = $failureDate->copy()->addDays(7);
        Carbon::setTestNow($recoveryDate);

        // Create a successful payment
        $recoveryCharge = $this->subscriptionChargeRepository->createCharge(
            $user->id,
            $recoveryDate,
            2200,
            'paid'
        );
        $recoveryCharge->payment_date = $recoveryDate;
        $recoveryCharge->save();

        // Run recovery process
        $this->recoverMemberships->run();

        $user->refresh();
        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->active);

        // Step 6: Second payment failure - no recovery this time
        $secondFailureDate = $currentDate->copy()->addMonths(7);
        Carbon::setTestNow($secondFailureDate);

        $secondFailedCharge = $this->subscriptionChargeRepository->createCharge(
            $user->id,
            $secondFailureDate,
            2200,
            'due'
        );

        $this->subChargeEventHandler->onPaymentFailure(
            $secondFailedCharge->id,
            $user->id,
            $secondFailureDate,
            2200
        );

        $user->refresh();
        $this->assertEquals('payment-warning', $user->status);

        // Step 7: Grace period expires - member suspended
        Carbon::setTestNow($secondFailureDate->copy()->addDays(11));
        $this->checkPaymentWarnings->run();

        $user->refresh();
        $this->assertEquals('suspended', $user->status);
        $this->assertFalse($user->active);
        $this->assertNotNull($user->suspended_at);

        // Step 8: 30 days after suspension - member marked as left
        Carbon::setTestNow(Carbon::now()->addDays(30));
        $this->checkSuspendedUsers->run();

        $user->refresh();
        $this->assertEquals('left', $user->status);
        $this->assertFalse($user->active);

        // Cleanup
        Carbon::setTestNow();
    }

    public function testMemberCancellationFlow()
    {
        // Step 1: Active member decides to cancel
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'payment_method' => 'gocardless-variable',
            'subscription_expires' => Carbon::now()->addMonth(),
        ]);

        // Step 2: Member cancels subscription
        $user->status = 'leaving';
        $user->payment_method = null;
        $user->save();

        $this->assertEquals('leaving', $user->status);
        $this->assertTrue($user->active); // Should still have access until expiry

        // Step 3: Subscription expires
        Carbon::setTestNow(Carbon::now()->addMonth()->addDay());
        $this->checkLeavingUsers->run();

        $user->refresh();
        $this->assertEquals('left', $user->status);
        $this->assertFalse($user->active);

        // Cleanup
        Carbon::setTestNow();
    }

    public function testMemberReactivationAfterSuspension()
    {
        // Step 1: Member is suspended
        $user = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'payment_method' => 'gocardless-variable',
            'monthly_subscription' => 2200,
            'mandate_id' => 'MA123456',
            'subscription_expires' => Carbon::now()->subDays(5),
            'suspended_at' => Carbon::now()->subDays(5),
        ]);

        // Step 2: Member makes a new payment to reactivate
        $reactivationCharge = $this->subscriptionChargeRepository->createCharge(
            $user->id,
            Carbon::now(),
            2200,
            'paid'
        );
        $reactivationCharge->payment_date = Carbon::now();
        $reactivationCharge->save();

        // Run recovery process
        $this->recoverMemberships->run();

        $user->refresh();
        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->active);
        $this->assertNull($user->suspended_at);
    }

    public function testHonoraryMemberExemption()
    {
        // Step 1: Create honorary member
        $user = factory(User::class)->create([
            'status' => 'honorary',
            'active' => true,
            'payment_method' => null,
            'subscription_expires' => Carbon::now()->subMonths(6), // Very expired
        ]);

        // Step 2: Run all checks (recovery should ignore special case users)
        $this->recoverMemberships->run();
        $this->checkPaymentWarnings->run();
        $this->checkSuspendedUsers->run();

        // Verify: Honorary member should be unaffected
        $user->refresh();
        $this->assertEquals('honorary', $user->status);
        $this->assertTrue($user->active);
    }

    public function testOnlineOnlyMemberFlow()
    {
        // Step 1: Create online-only member
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'online_only' => true,
            'payment_method' => 'gocardless-variable',
            'subscription_expires' => Carbon::now()->addMonth(),
        ]);

        // Step 2: Payment failure
        $charge = $this->subscriptionChargeRepository->createCharge(
            $user->id,
            Carbon::now(),
            2200,
            'due'
        );

        $this->subChargeEventHandler->onPaymentFailure(
            $charge->id,
            $user->id,
            Carbon::now(),
            2200
        );

        // Verify: Online-only member should follow same payment warning flow
        $user->refresh();
        $this->assertEquals('payment-warning', $user->status);
        $this->assertTrue((bool)$user->active);
        $this->assertTrue((bool)$user->online_only);

        // Step 3: Grace period expires
        Carbon::setTestNow(Carbon::now()->addDays(11));
        $this->checkPaymentWarnings->run();

        $user->refresh();
        $this->assertEquals('suspended', $user->status);
        $this->assertFalse((bool)$user->active);
        $this->assertTrue((bool)$user->online_only); // Should preserve online_only flag

        // Cleanup
        Carbon::setTestNow();
    }

    public function testMemberWithDifferentSubscriptionAmounts()
    {
        $subscriptionAmounts = [1700, 2200, 2700]; // Low income, standard, supporter

        foreach ($subscriptionAmounts as $amount) {
            $user = factory(User::class)->create([
                'status' => 'active',
                'active' => true,
                'payment_method' => 'gocardless-variable',
                'monthly_subscription' => $amount,
                'subscription_expires' => Carbon::now()->addMonth(),
            ]);

            // Payment failure
            $charge = $this->subscriptionChargeRepository->createCharge(
                $user->id,
                Carbon::now(),
                $amount,
                'due'
            );

            $this->subChargeEventHandler->onPaymentFailure(
                $charge->id,
                $user->id,
                Carbon::now(),
                $amount
            );

            // Verify: All subscription amounts should get same treatment
            $user->refresh();
            $this->assertEquals('payment-warning', $user->status);
            $this->assertTrue($user->active);
            $this->assertEquals($amount, $user->monthly_subscription);
        }
    }

    public function testMembershipExpiryCascade()
    {
        // Create multiple members in different states
        $users = collect([
            // Member 1: Active, will expire naturally
            factory(User::class)->create([
                'status' => 'active',
                'active' => true,
                'subscription_expires' => Carbon::now()->subDays(20),
                'payment_method' => 'gocardless-variable',
            ]),
            // Member 2: Payment warning, will be suspended
            factory(User::class)->create([
                'status' => 'payment-warning',
                'active' => true,
                'subscription_expires' => Carbon::now()->subDays(1),
                'payment_method' => 'gocardless-variable',
            ]),
            // Member 3: Suspended, will be marked as left
            factory(User::class)->create([
                'status' => 'suspended',
                'active' => false,
                'suspended_at' => Carbon::now()->subDays(31),
                'payment_method' => 'gocardless-variable',
            ]),
        ]);

        // Run all checks in order
        $this->recoverMemberships->run();
        $this->checkPaymentWarnings->run();
        $this->checkSuspendedUsers->run();

        // Verify cascading effects
        $users[0]->refresh();
        $users[1]->refresh();
        $users[2]->refresh();

        // Member 1: Should remain active (recovery might have happened)
        $this->assertContains($users[0]->status, ['active', 'suspended']);

        // Member 2: Should be suspended
        $this->assertEquals('suspended', $users[1]->status);
        $this->assertFalse($users[1]->active);

        // Member 3: Should be marked as left
        $this->assertEquals('left', $users[2]->status);
        $this->assertFalse($users[2]->active);
    }


    public function testDataIntegrityThroughoutLifecycle()
    {
        // Create member with comprehensive data
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'payment_method' => 'gocardless-variable',
            'monthly_subscription' => 2200,
            'payment_day' => 15,
            'subscription_expires' => Carbon::now()->addMonth(),
            'display_name' => 'Test Member',
            'given_name' => 'Test',
            'family_name' => 'Member',
            'email' => 'test@example.com',
        ]);

        $originalData = [
            'monthly_subscription' => $user->monthly_subscription,
            'payment_day' => $user->payment_day,
            'display_name' => $user->display_name,
            'given_name' => $user->given_name,
            'family_name' => $user->family_name,
            'email' => $user->email,
        ];

        // Go through complete lifecycle
        $charge = $this->subscriptionChargeRepository->createCharge(
            $user->id,
            Carbon::now(),
            2200,
            'due'
        );

        // Payment failure
        $this->subChargeEventHandler->onPaymentFailure(
            $charge->id,
            $user->id,
            Carbon::now(),
            2200
        );

        $user->refresh();
        $this->verifyDataIntegrity($user, $originalData);

        // Suspension
        Carbon::setTestNow(Carbon::now()->addDays(11));
        $this->checkPaymentWarnings->run();

        $user->refresh();
        $this->verifyDataIntegrity($user, $originalData);

        // Marked as left
        Carbon::setTestNow(Carbon::now()->addDays(30));
        $this->checkSuspendedUsers->run();

        $user->refresh();
        $this->verifyDataIntegrity($user, $originalData);

        // Cleanup
        Carbon::setTestNow();
    }

    private function verifyDataIntegrity(User $user, array $originalData)
    {
        $this->assertEquals($originalData['monthly_subscription'], $user->monthly_subscription);
        $this->assertEquals($originalData['payment_day'], $user->payment_day);
        $this->assertEquals($originalData['display_name'], $user->display_name);
        $this->assertEquals($originalData['given_name'], $user->given_name);
        $this->assertEquals($originalData['family_name'], $user->family_name);
        $this->assertEquals($originalData['email'], $user->email);
    }
}