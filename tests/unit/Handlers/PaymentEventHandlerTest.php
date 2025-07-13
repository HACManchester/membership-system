<?php

use BB\Entities\User;
use BB\Entities\Payment;
use BB\Entities\SubscriptionCharge;
use BB\Exceptions\PaymentException;
use BB\Handlers\PaymentEventHandler;
use BB\Repo\PaymentRepository;
use BB\Repo\SubscriptionChargeRepository;
use Carbon\Carbon;
use Tests\TestCase;

class PaymentEventHandlerTest extends TestCase
{
    private $handler;
    private $paymentRepository;
    private $subscriptionChargeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->paymentRepository = app(PaymentRepository::class);
        $this->subscriptionChargeRepository = app(SubscriptionChargeRepository::class);

        $this->handler = new PaymentEventHandler(
            $this->paymentRepository,
            $this->subscriptionChargeRepository
        );
    }

    public function testOnCreateWithBalancePaymentUpdatesBalance()
    {
        $user = factory(User::class)->create([
            'cash_balance' => 1000,
        ]);

        // Previous payment for the initial balance
        factory(Payment::class)->create([
            'user_id' => $user->id,
            'reason' => 'balance',
            'amount' => 10,
            'status' => 'paid',
        ]);

        $payment = factory(Payment::class)->create([
            'source' => 'gocardless-variable',
            'user_id' => $user->id,
            'reason' => 'balance',
            'amount' => 5,
            'status' => 'paid',
        ]);

        $this->handler->onCreate(
            $user->id,
            'balance',
            '',
            $payment->id,
            'paid'
        );

        $this->assertEquals(1500, $user->refresh()->cash_balance);
    }

    public function testOnCreateWithSubscriptionPaymentLinksToCharge()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
        ]);

        $charge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'status' => 'due',
            'amount' => 0, // Will be updated from payment
        ]);

        $payment = factory(Payment::class)->create([
            'source' => 'gocardless-variable',
            'user_id' => $user->id,
            'reason' => 'subscription',
            'amount' => 2200,
            'status' => 'pending',
            'reference' => '', // Empty reference
        ]);

        $this->handler->onCreate(
            $user->id,
            'subscription',
            '',
            $payment->id,
            'pending'
        );

        $payment->refresh();
        $charge->refresh();

        $this->assertEquals($charge->id, $payment->reference);
        $this->assertEquals('processing', $charge->status);
        $this->assertEquals(2200, $charge->amount);
    }

    public function testOnCreateWithSubscriptionPaymentMarksChargeAsPaid()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
        ]);

        $charge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'status' => 'due',
            'amount' => 0,
        ]);

        $payment = factory(Payment::class)->create([
            'source' => 'gocardless-variable',
            'user_id' => $user->id,
            'reason' => 'subscription',
            'amount' => 2200,
            'status' => 'paid',
            'reference' => '',
            'paid_at' => Carbon::now(),
        ]);

        $this->handler->onCreate(
            $user->id,
            'subscription',
            '',
            $payment->id,
            'paid'
        );

        $charge->refresh();
        $this->assertEquals('paid', $charge->status);
        $this->assertEquals(2200, $charge->amount);
    }

    public function testOnCreateWithExistingReferenceValidatesTheReferencesMatch()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
        ]);

        $charge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'status' => 'due',
        ]);

        $payment = factory(Payment::class)->create([
            'source' => 'gocardless-variable',
            'user_id' => $user->id,
            'reason' => 'subscription',
            'amount' => 2200,
            'status' => 'pending',
            'reference' => $charge->id, // Already linked
        ]);

        // No exception should be thrown
        $this->handler->onCreate(
            $user->id,
            'subscription',
            '',
            $payment->id,
            'pending'
        );

        $charge->refresh();
        $this->assertEquals('processing', $charge->status);
    }

    public function testOnCreateWithMismatchedReferenceThrowsException()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
        ]);

        // Create charge with earlier date (will be found first by findCharge)
        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'status' => 'due',
            'charge_date' => Carbon::now()->subDays(2),
        ]);

        $laterCharge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'status' => 'due',
            'charge_date' => Carbon::now()->subDays(1),
        ]);

        // Create payment with reference to laterCharge, but findCharge will return charge1 (earlier date)
        $payment = factory(Payment::class)->create([
            'source' => 'gocardless-variable',
            'user_id' => $user->id,
            'reason' => 'subscription',
            'amount' => 2200,
            'status' => 'pending',
            'reference' => $laterCharge->id,
        ]);

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Attempting to update sub charge');

        $this->handler->onCreate(
            $user->id,
            'subscription',
            '',
            $payment->id,
            'pending'
        );
    }

    public function testOnCancelMarksSubscriptionChargeAsCancelled()
    {
        $user = factory(User::class)->create();

        $charge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'status' => 'processing',
            'amount' => 2200,
        ]);

        $payment = factory(Payment::class)->create([
            'source' => 'gocardless-variable',
            'user_id' => $user->id,
            'reason' => 'subscription',
            'status' => 'cancelled',
            'reference' => $charge->id,
        ]);

        $this->handler->onCancel(
            $payment->id,
            $user->id,
            'subscription',
            $charge->id,
            'cancelled'
        );

        $charge->refresh();
        $this->assertEquals('cancelled', $charge->status);
        $this->assertEquals(0, $charge->amount);
        $this->assertNull($charge->payment_date);
    }

    public function testOnCancelHandlesEmptyReferenceGracefully()
    {
        $user = factory(User::class)->create();

        $payment = factory(Payment::class)->create([
            'source' => 'gocardless-variable',
            'user_id' => $user->id,
            'reason' => 'subscription',
            'status' => 'cancelled',
            'reference' => '',
        ]);

        $this->handler->onCancel(
            $payment->id,
            $user->id,
            'subscription',
            '', // Empty reference
            'cancelled'
        );

        // Verify no exception was thrown and payment remains unchanged
        $payment->refresh();
        $this->assertEquals('cancelled', $payment->status);
        $this->assertEmpty($payment->reference);
    }

    public function testOnCancelIgnoresNonSubscriptionPayments()
    {
        $user = factory(User::class)->create();

        $payment = factory(Payment::class)->create([
            'source' => 'gocardless-variable',
            'user_id' => $user->id,
            'reason' => 'balance',
            'status' => 'cancelled',
        ]);

        $originalStatus = $payment->status;

        $this->handler->onCancel(
            $payment->id,
            $user->id,
            'balance',
            '',
            'cancelled'
        );

        // Verify payment remains unchanged (since it's not a subscription payment)
        $payment->refresh();
        $this->assertEquals($originalStatus, $payment->status);
    }

    public function testOnPaidMarksSubscriptionChargeAsPaid()
    {
        $user = factory(User::class)->create();
        $paymentDate = Carbon::now();

        $charge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'status' => 'processing',
        ]);

        $payment = factory(Payment::class)->create([
            'source' => 'gocardless-variable',
            'user_id' => $user->id,
            'reason' => 'subscription',
            'status' => 'paid',
            'reference' => $charge->id,
            'paid_at' => $paymentDate,
        ]);

        $this->handler->onPaid(
            $user->id,
            $payment->id,
            'subscription',
            $charge->id,
            $paymentDate
        );

        $charge->refresh();
        $this->assertEquals('paid', $charge->status);
        $this->assertNotNull($charge->payment_date);
        $this->assertTrue($charge->payment_date->isSameDay($paymentDate));
    }

    public function testOnPaidIgnoresNonSubscriptionPayments()
    {
        $user = factory(User::class)->create();
        $paymentDate = Carbon::now();

        $payment = factory(Payment::class)->create([
            'source' => 'gocardless-variable',
            'user_id' => $user->id,
            'reason' => 'balance',
            'status' => 'paid',
            'paid_at' => $paymentDate,
        ]);

        $originalStatus = $payment->status;
        $originalPaidAt = $payment->paid_at;

        $this->handler->onPaid(
            $user->id,
            $payment->id,
            'balance',
            '',
            $paymentDate
        );

        // Verify payment remains unchanged (since it's not a subscription payment)
        $payment->refresh();
        $this->assertEquals($originalStatus, $payment->status);
        $this->assertTrue($payment->paid_at->eq($originalPaidAt));
    }

    public function testOnPaidIgnoresPaymentsWithoutReference()
    {
        $user = factory(User::class)->create();
        $paymentDate = Carbon::now();

        $payment = factory(Payment::class)->create([
            'source' => 'gocardless-variable',
            'user_id' => $user->id,
            'reason' => 'subscription',
            'status' => 'paid',
            'reference' => '', // No reference
            'paid_at' => $paymentDate,
        ]);

        $originalStatus = $payment->status;

        $this->handler->onPaid(
            $user->id,
            $payment->id,
            'subscription',
            '', // Empty reference
            $paymentDate
        );

        // Verify payment remains unchanged (since no reference to process)
        $payment->refresh();
        $this->assertEquals($originalStatus, $payment->status);
        $this->assertEmpty($payment->reference);
    }

    public function testOnDeleteWithBalancePaymentUpdatesBalance()
    {
        $user = factory(User::class)->create([
            'cash_balance' => 1500,
        ]);
        
        // Previous payment for the initial balance
        factory(Payment::class)->create([
            'source' => 'gocardless-variable',
            'user_id' => $user->id,
            'reason' => 'balance',
            'amount' => 10,
            'status' => 'paid',
        ]);

        $payment = factory(Payment::class)->create([
            'source' => 'gocardless-variable',
            'user_id' => $user->id,
            'reason' => 'balance',
            'amount' => 5,
            'status' => 'cancelled',
        ]);

        // Call onDelete - should trigger balance recalculation
        $this->handler->onDelete(
            $user->id,
            'gocardless-variable',
            'balance',
            $payment->id
        );

        $this->assertEquals(1000, $user->refresh()->cash_balance);
    }

    public function testOnDeleteWithSubscriptionPaymentDoesNothing()
    {
        $user = factory(User::class)->create();

        $payment = factory(Payment::class)->create([
            'source' => 'gocardless-variable',
            'user_id' => $user->id,
            'reason' => 'subscription',
            'status' => 'cancelled',
        ]);

        $originalStatus = $payment->status;

        $this->handler->onDelete(
            $user->id,
            'gocardless-variable',
            'subscription',
            $payment->id
        );

        // Verify payment remains unchanged (onDelete doesn't process subscription payments)
        $payment->refresh();
        $this->assertEquals($originalStatus, $payment->status);
    }
}
