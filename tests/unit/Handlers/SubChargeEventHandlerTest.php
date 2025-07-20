<?php

use BB\Entities\User;
use BB\Entities\Payment;
use BB\Handlers\SubChargeEventHandler;
use BB\Repo\UserRepository;
use BB\Repo\SubscriptionChargeRepository;
use Carbon\Carbon;
use Tests\TestCase;

class SubChargeEventHandlerTest extends TestCase
{
    private $handler;
    private $userRepository;
    private $subscriptionChargeRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = app(UserRepository::class);
        $this->subscriptionChargeRepository = app(SubscriptionChargeRepository::class);
        $this->handler = new SubChargeEventHandler($this->userRepository);
    }

    public function testOnPaymentFailureSetsPaymentWarning()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'payment_method' => 'gocardless-variable',
        ]);

        $subscriptionCharge = $this->subscriptionChargeRepository->createCharge(
            $user->id,
            Carbon::now(),
            2200,
            'due'
        );

        $this->handler->onPaymentFailure(
            $subscriptionCharge->id,
            $user->id,
            Carbon::now(),
            2200
        );

        $user->refresh();
        $this->assertEquals('payment-warning', $user->status);
        $this->assertTrue($user->active);
        
        // Should set 10-day grace period
        $expectedExpiry = Carbon::now()->addDays(10);
        $this->assertTrue($user->subscription_expires->isSameDay($expectedExpiry));
    }

    public function testOnPaymentFailureDoesNotSetWarningWhenOtherPaymentsOutstanding()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'payment_method' => 'gocardless-variable',
        ]);

        $subscriptionCharge = $this->subscriptionChargeRepository->createCharge(
            $user->id,
            Carbon::now(),
            2200,
            'due'
        );

        // Create another payment for the same charge that's still pending
        factory(Payment::class)->create([
            'user_id' => $user->id,
            'reference' => $subscriptionCharge->id,
            'status' => 'pending',
            'reason' => 'subscription',
        ]);

        $originalStatus = $user->status;
        $originalExpiry = $user->subscription_expires;

        $this->handler->onPaymentFailure(
            $subscriptionCharge->id,
            $user->id,
            Carbon::now(),
            2200
        );

        $user->refresh();
        
        // Should not change status since other payment exists
        $this->assertEquals($originalStatus, $user->status);
        $this->assertEquals($originalExpiry, $user->subscription_expires);
    }

    public function testOnPaymentFailureIgnoresPreviouslyFailedPayments()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'payment_method' => 'gocardless-variable',
        ]);

        $subscriptionCharge = $this->subscriptionChargeRepository->createCharge(
            $user->id,
            Carbon::now(),
            2200,
            'due'
        );

        // Create a failed payment (shouldn't prevent warning)
        factory(Payment::class)->create([
            'user_id' => $user->id,
            'reference' => $subscriptionCharge->id,
            'status' => 'failed',
            'reason' => 'subscription',
        ]);

        // Create a cancelled payment (shouldn't prevent warning)  
        factory(Payment::class)->create([
            'user_id' => $user->id,
            'reference' => $subscriptionCharge->id,
            'status' => 'cancelled',
            'reason' => 'subscription',
        ]);

        $this->handler->onPaymentFailure(
            $subscriptionCharge->id,
            $user->id,
            Carbon::now(),
            2200
        );

        $user->refresh();
        
        // Should set warning since no active payments exist
        $this->assertEquals('payment-warning', $user->status);
    }
}