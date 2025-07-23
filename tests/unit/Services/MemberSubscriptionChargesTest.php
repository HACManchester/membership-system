<?php

use BB\Entities\User;
use BB\Entities\SubscriptionCharge;
use BB\Entities\Payment;
use BB\Helpers\GoCardlessHelper;
use BB\Services\MemberSubscriptionCharges;
use BB\Repo\PaymentRepository;
use BB\Repo\SubscriptionChargeRepository;
use BB\Repo\UserRepository;
use Carbon\Carbon;
use Tests\TestCase;

class MemberSubscriptionChargesTest extends TestCase
{
    private $service;
    private $mockGoCardless;
    private $userRepository;
    private $subscriptionChargeRepository;
    private $paymentRepository;

    public function setUp(): void
    {
        parent::setUp();
        
        // Get real repositories for database interactions
        $this->userRepository = app(UserRepository::class);
        $this->subscriptionChargeRepository = app(SubscriptionChargeRepository::class);
        $this->paymentRepository = app(PaymentRepository::class);
        
        // Mock GoCardless and Telegram to avoid external calls
        $this->mockGoCardless = $this->createMock(GoCardlessHelper::class);
        $mockTelegramHelper = $this->createMock(\BB\Helpers\TelegramHelper::class);
        $this->app->instance(\BB\Helpers\TelegramHelper::class, $mockTelegramHelper);
        
        $this->service = new MemberSubscriptionCharges(
            $this->userRepository,
            $this->subscriptionChargeRepository,
            $this->mockGoCardless,
            $this->paymentRepository
        );
    }

    public function testCreateSubscriptionChargesForMatchingPaymentDay()
    {
        $paymentDay = 15; // Use a day that exists in all months
        $otherDay = 10; // Different payment day
        
        // Create active users with payment day matching target date
        $user1 = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => $paymentDay,
            'monthly_subscription' => 22,
        ]);

        $user2 = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => $paymentDay,
            'monthly_subscription' => 17,
        ]);

        // Create user with different payment day (should be ignored)
        $user3 = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => $otherDay,
            'monthly_subscription' => 27,
        ]);

        // Set target date to match the payment day
        $targetDate = Carbon::now()->setDay($paymentDay);
        
        $this->service->createSubscriptionCharges($targetDate);

        // Verify charges were created for matching users
        $charge1 = SubscriptionCharge::where('user_id', $user1->id)
            ->whereDate('charge_date', $targetDate)
            ->first();
        $this->assertNotNull($charge1);
        $this->assertEquals(22, $charge1->amount);
        $this->assertEquals('pending', $charge1->status);

        $charge2 = SubscriptionCharge::where('user_id', $user2->id)
            ->whereDate('charge_date', $targetDate)
            ->first();
        $this->assertNotNull($charge2);
        $this->assertEquals(17, $charge2->amount);

        // Verify no charge created for non-matching payment day
        $charge3 = SubscriptionCharge::where('user_id', $user3->id)
            ->whereDate('charge_date', $targetDate)
            ->first();
        $this->assertNull($charge3);
    }

    public function testCreateSubscriptionChargesIgnoresInactiveUsers()
    {
        $paymentDay = 15; // Use a day that exists in all months
        $targetDate = Carbon::now()->setDay($paymentDay);
        
        $suspendedUser = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'payment_day' => $paymentDay,
            'monthly_subscription' => 22,
        ]);

        $leftUser = factory(User::class)->create([
            'status' => 'left',
            'active' => false,
            'payment_day' => $paymentDay,
            'monthly_subscription' => 22,
        ]);

        $this->service->createSubscriptionCharges($targetDate);

        $suspendedCharge = SubscriptionCharge::where('user_id', $suspendedUser->id)
            ->whereDate('charge_date', $targetDate)
            ->count();
        $this->assertEquals(0, $suspendedCharge);

        $leftCharge = SubscriptionCharge::where('user_id', $leftUser->id)
            ->whereDate('charge_date', $targetDate)
            ->count();
        $this->assertEquals(0, $leftCharge);
    }

    public function testCreateSubscriptionChargesPreventsDuplicates()
    {
        $paymentDay = 15; // Use a day that exists in all months
        $targetDate = Carbon::now()->setDay($paymentDay);
        
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'payment_day' => $paymentDay,
            'monthly_subscription' => 22,
        ]);

        $this->service->createSubscriptionCharges($targetDate);
        $this->service->createSubscriptionCharges($targetDate);

        $chargeCount = SubscriptionCharge::where('user_id', $user->id)
            ->whereDate('charge_date', $targetDate)
            ->count();
        $this->assertEquals(1, $chargeCount);
    }

    public function testMakeChargesDueUpdatesChargesForCurrentAndPastDates()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
        ]);

        $todayCharge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'status' => 'pending',
        ]);

        $pastCharge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->subDays(2),
            'status' => 'pending',
        ]);

        $futureCharge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->addDays(2),
            'status' => 'pending',
        ]);

        $this->service->makeChargesDue();

        $todayCharge->refresh();
        $this->assertEquals('due', $todayCharge->status);

        $pastCharge->refresh();
        $this->assertEquals('due', $pastCharge->status);

        $futureCharge->refresh();
        $this->assertEquals('pending', $futureCharge->status);
    }

    public function testBillMembersCreatesGoCardlessPaymentsForCorrectAmounts()
    {
        $user1 = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'monthly_subscription' => 22,
            'mandate_id' => 'MD123456789',
        ]);

        $user2 = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'monthly_subscription' => 17,
            'mandate_id' => 'MD987654321',
        ]);

        $charge1 = factory(SubscriptionCharge::class)->create([
            'user_id' => $user1->id,
            'charge_date' => Carbon::now(),
            'amount' => 0,
            'status' => 'due',
        ]);

        $charge2 = factory(SubscriptionCharge::class)->create([
            'user_id' => $user2->id,
            'charge_date' => Carbon::now(),
            'amount' => 0,
            'status' => 'due',
        ]);

        $mockBill1 = (object)['id' => 'PM123456789', 'status' => 'pending_submission'];
        $mockBill2 = (object)['id' => 'PM987654321', 'status' => 'pending_submission'];

        $this->mockGoCardless->expects($this->exactly(2))
            ->method('newBill')
            ->withConsecutive(
                ['MD123456789', 2200, 'Monthly subscription'],
                ['MD987654321', 1700, 'Monthly subscription'] 
            )
            ->willReturnOnConsecutiveCalls($mockBill1, $mockBill2);

        $this->mockGoCardless->expects($this->exactly(2))
            ->method('getNameFromReason')
            ->with('subscription')
            ->willReturn('Monthly subscription');

        $this->service->billMembers();

        $payment1 = Payment::where('user_id', $user1->id)
            ->where('reference', $charge1->id)
            ->first();
        $this->assertNotNull($payment1);
        $this->assertEquals(22, $payment1->amount);
        $this->assertEquals('pending', $payment1->status);
        $this->assertEquals('PM123456789', $payment1->source_id);

        $payment2 = Payment::where('user_id', $user2->id)
            ->where('reference', $charge2->id)
            ->first();
        $this->assertNotNull($payment2);
        $this->assertEquals(17, $payment2->amount);
        $this->assertEquals('pending', $payment2->status);
        $this->assertEquals('PM987654321', $payment2->source_id);
    }

    public function testBillMembersUsesChargeAmountWhenAvailable()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'monthly_subscription' => 22,
            'mandate_id' => 'MD123456789',
        ]);

        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'amount' => 27,
            'status' => 'due',
        ]);

        $mockBill = (object)['id' => 'PM123456789', 'status' => 'pending_submission'];

        $this->mockGoCardless->expects($this->once())
            ->method('newBill')
            ->with('MD123456789', 2700, 'Monthly subscription')
            ->willReturn($mockBill);

        $this->mockGoCardless->expects($this->once())
            ->method('getNameFromReason')
            ->willReturn('Monthly subscription');

        $this->service->billMembers();

        $payment = Payment::where('user_id', $user->id)->first();
        $this->assertEquals(27, $payment->amount);
    }

    public function testBillMembersOnlyProcessesGoCardlessUsers()
    {
        $goCardlessUser = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'monthly_subscription' => 22,
            'mandate_id' => 'MD123456789',
        ]);

        $balanceUser = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'balance',
            'monthly_subscription' => 22,
        ]);

        factory(SubscriptionCharge::class)->create([
            'user_id' => $goCardlessUser->id,
            'charge_date' => Carbon::now(),
            'status' => 'due',
        ]);

        factory(SubscriptionCharge::class)->create([
            'user_id' => $balanceUser->id,
            'charge_date' => Carbon::now(),
            'status' => 'due',
        ]);

        $mockBill = (object)['id' => 'PM123456789', 'status' => 'pending_submission'];

        $this->mockGoCardless->expects($this->once())
            ->method('newBill')
            ->willReturn($mockBill);

        $this->mockGoCardless->expects($this->once())
            ->method('getNameFromReason')
            ->willReturn('Monthly subscription');

        $this->service->billMembers();

        $goCardlessPayment = Payment::where('user_id', $goCardlessUser->id)->first();
        $this->assertNotNull($goCardlessPayment);

        $balancePayment = Payment::where('user_id', $balanceUser->id)->first();
        $this->assertNull($balancePayment);
    }

    public function testBillMembersHandlesGoCardlessFailures()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'monthly_subscription' => 22,
            'mandate_id' => 'MD123456789',
        ]);

        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'amount' => 0,
            'status' => 'due',
        ]);

        $this->mockGoCardless->expects($this->once())
            ->method('newBill')
            ->willThrowException(new \GoCardlessPro\Core\Exception\ValidationFailedException('Invalid mandate'));

        $this->mockGoCardless->expects($this->once())
            ->method('getNameFromReason')
            ->willReturn('Monthly subscription');

        $this->service->billMembers();

        $payment = Payment::where('user_id', $user->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('failed', $payment->status);
        $this->assertNull($payment->source_id);
        $this->assertEquals(22, $payment->amount);
    }

    public function testBillMembersSkipsChargesWithExistingPayments()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'monthly_subscription' => 22,
            'mandate_id' => 'MD123456789',
        ]);

        $charge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'status' => 'due',
        ]);

        factory(Payment::class)->create([
            'user_id' => $user->id,
            'reference' => (string)$charge->id,
            'source' => 'gocardless-variable',
            'status' => 'failed',
            'reason' => 'subscription',
        ]);

        $this->mockGoCardless->expects($this->never())
            ->method('newBill');

        $this->service->billMembers();

        $paymentCount = Payment::where('user_id', $user->id)
            ->where('reference', $charge->id)
            ->count();
        $this->assertEquals(1, $paymentCount);
    }

    public function testBillMembersHandlesInvalidStateException()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'monthly_subscription' => 22,
            'mandate_id' => 'MD123456789',
        ]);

        factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'status' => 'due',
        ]);

        $this->mockGoCardless->expects($this->once())
            ->method('newBill')
            ->willThrowException(new \GoCardlessPro\Core\Exception\InvalidStateException('Payment cannot be created'));

        $this->mockGoCardless->expects($this->once())
            ->method('getNameFromReason')
            ->willReturn('Monthly subscription');

        $this->service->billMembers();

        $payment = Payment::where('user_id', $user->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('failed', $payment->status);
    }
}