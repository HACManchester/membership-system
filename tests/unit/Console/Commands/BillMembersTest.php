<?php

use BB\Entities\User;
use BB\Entities\SubscriptionCharge;
use BB\Entities\Payment;
use BB\Helpers\GoCardlessHelper;
use Carbon\Carbon;
use Tests\TestCase;

class BillMembersTest extends TestCase
{
    private $mockGoCardless;

    public function setUp(): void
    {
        parent::setUp();

        $telegramHelper = $this->createMock(\BB\Helpers\TelegramHelper::class);
        $this->app->instance(\BB\Helpers\TelegramHelper::class, $telegramHelper);

        $this->mockGoCardless = $this->createMock(GoCardlessHelper::class);
        $this->app->instance(GoCardlessHelper::class, $this->mockGoCardless);
    }

    public function testBillsCreatedForCorrectAmountFromUserSubscription()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'monthly_subscription' => 22,
            'mandate_id' => 'MD123456789',
            'display_name' => 'Test User',
        ]);

        $charge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'amount' => 0,
            'status' => 'due',
        ]);

        $mockBill = new \stdClass();
        $mockBill->id = 'PM123456789';
        $mockBill->status = 'pending_submission';

        $this->mockGoCardless->expects($this->once())
            ->method('newBill')
            ->with('MD123456789', 2200, 'Monthly subscription')
            ->willReturn($mockBill);

        $this->mockGoCardless->expects($this->once())
            ->method('getNameFromReason')
            ->with('subscription')
            ->willReturn('Monthly subscription');

        $this->artisan('bb:bill-members')->assertExitCode(0);

        $payment = Payment::where('user_id', $user->id)
            ->where('reference', $charge->id)
            ->first();

        $this->assertNotNull($payment);
        $this->assertEquals(22, $payment->amount);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals('PM123456789', $payment->source_id);
    }

    public function testBillsCreatedUsingChargeAmountIfSet()
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

        $mockBill = (object)['id' => 'PM987654321', 'status' => 'pending_submission'];
        $this->mockGoCardless->expects($this->once())
            ->method('newBill')
            ->with(
                'MD123456789',
                2700,
                'Monthly subscription'
            )
            ->willReturn($mockBill);

        $this->mockGoCardless->expects($this->once())
            ->method('getNameFromReason')
            ->with('subscription')
            ->willReturn('Monthly subscription');

        $this->artisan('bb:bill-members')->assertExitCode(0);

        $payment = Payment::where('user_id', $user->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(27, $payment->amount);
    }

    public function testMultipleDifferentAmountsBilled()
    {
        $users = [];
        $charges = [];
        $expectedAmounts = [17, 22, 27];

        foreach ($expectedAmounts as $index => $amount) {
            $user = factory(User::class)->create([
                'status' => 'active',
                'payment_method' => 'gocardless-variable',
                'monthly_subscription' => $amount,
                'mandate_id' => "MD{$index}",
            ]);

            $charge = factory(SubscriptionCharge::class)->create([
                'user_id' => $user->id,
                'charge_date' => Carbon::now(),
                'amount' => 0,
                'status' => 'due',
            ]);

            $users[] = $user;
            $charges[] = $charge;
        }

        $this->mockGoCardless->expects($this->exactly(3))
            ->method('newBill')
            ->withConsecutive(
                ['MD0', 1700, 'Monthly subscription'],
                ['MD1', 2200, 'Monthly subscription'],
                ['MD2', 2700, 'Monthly subscription'] 
            )
            ->willReturnOnConsecutiveCalls(
                (object)['id' => 'PM1', 'status' => 'pending_submission'],
                (object)['id' => 'PM2', 'status' => 'pending_submission'],
                (object)['id' => 'PM3', 'status' => 'pending_submission']
            );

        $this->mockGoCardless->expects($this->exactly(3))
            ->method('getNameFromReason')
            ->willReturn('Monthly subscription');

        $this->artisan('bb:bill-members')->assertExitCode(0);

        foreach ($expectedAmounts as $index => $expectedAmount) {
            $payment = Payment::where('user_id', $users[$index]->id)->first();
            $this->assertNotNull($payment, "Payment not found for user {$index}");
            $this->assertEquals($expectedAmount, $payment->amount, "Wrong amount for user {$index}");
        }
    }

    public function testGoCardlessFailureRecorded()
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

        $this->artisan('bb:bill-members')->assertExitCode(0);

        $payment = Payment::where('user_id', $user->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('failed', $payment->status);
        $this->assertNull($payment->source_id); // No GoCardless payment ID
    }

    public function testOnlyGoCardlessUsersAreBilled()
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

        $this->mockGoCardless->expects($this->once())
            ->method('newBill')
            ->willReturn((object)['id' => 'PM123', 'status' => 'pending_submission']);

        $this->mockGoCardless->expects($this->once())
            ->method('getNameFromReason')
            ->willReturn('Monthly subscription');

        $this->artisan('bb:bill-members')->assertExitCode(0);

        $goCardlessPayment = Payment::where('user_id', $goCardlessUser->id)->first();
        $this->assertNotNull($goCardlessPayment);

        $balancePayment = Payment::where('user_id', $balanceUser->id)->first();
        $this->assertNull($balancePayment);
    }

    public function testChargesWithExistingPaymentsSkipped()
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
            'reference' => $charge->id,
            'source' => 'gocardless-variable',
            'status' => 'failed',
            'amount' => 22,
            'fee' => 0,
            'amount_minus_fee' => 22,
            'reason' => 'subscription',
        ]);

        $this->mockGoCardless->expects($this->never())
            ->method('newBill');

        $this->artisan('bb:bill-members')->assertExitCode(0);

        $paymentCount = Payment::where('user_id', $user->id)
            ->where('reference', $charge->id)
            ->count();
        $this->assertEquals(1, $paymentCount); // Only the existing one
    }
}
