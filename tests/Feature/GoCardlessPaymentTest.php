<?php

namespace Tests\Feature;

use BB\Entities\Payment;
use BB\Entities\SubscriptionCharge;
use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoCardlessPaymentTest extends TestCase
{
    use RefreshDatabase;

    private $goCardless;

    public function setUp(): void
    {
        parent::setUp();

        $this->goCardless = $this->createMock(GoCardlessHelper::class);
        $this->app->instance(GoCardlessHelper::class, $this->goCardless);
    }

    private function memberWithDueCharge($monthlySubscription = 22)
    {
        $member = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'monthly_subscription' => $monthlySubscription,
            'mandate_id' => 'MD123456789',
        ]);

        $charge = factory(SubscriptionCharge::class)->create([
            'user_id' => $member->id,
            'charge_date' => Carbon::now(),
            'amount' => $monthlySubscription,
            'status' => 'due',
        ]);

        return [$member, $charge];
    }

    /** @test */
    public function a_member_can_retry_their_subscription_payment()
    {
        [$member, $charge] = $this->memberWithDueCharge();

        $this->goCardless->method('newBill')
            ->willReturn((object)['id' => 'PM123', 'status' => 'pending_submission', 'amount' => 2200]);
        $this->goCardless->method('getNameFromReason')->willReturn('Monthly subscription');

        $this->actingAs($member)
            ->postJson(route('account.payment.gocardless.create', $member->id), [
                'reason' => 'subscription',
                'amount' => '2200',
            ])
            ->assertStatus(200);

        $payment = Payment::where('user_id', $member->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals('PM123', $payment->source_id);
        $this->assertEquals($charge->id, $payment->reference);

        $charge->refresh();
        $this->assertEquals('processing', $charge->status);
    }

    /** @test */
    public function a_subscription_payment_below_the_monthly_amount_is_rejected()
    {
        [$member, $charge] = $this->memberWithDueCharge(22);

        $this->goCardless->expects($this->never())->method('newBill');

        $this->actingAs($member)
            ->postJson(route('account.payment.gocardless.create', $member->id), [
                'reason' => 'subscription',
                'amount' => '100', // £1 against a £22 subscription
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('amount');

        $charge->refresh();
        $this->assertEquals('due', $charge->status);
        $this->assertEquals(0, Payment::where('user_id', $member->id)->count());
    }

    /** @test */
    public function unknown_reasons_and_invalid_amounts_are_rejected()
    {
        [$member] = $this->memberWithDueCharge();

        $this->goCardless->expects($this->never())->method('newBill');

        $this->actingAs($member)
            ->postJson(route('account.payment.gocardless.create', $member->id), [
                'reason' => 'balance',
                'amount' => '2200',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('reason');

        $this->actingAs($member)
            ->postJson(route('account.payment.gocardless.create', $member->id), [
                'reason' => 'subscription',
                'amount' => 'twenty',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function a_member_cannot_pay_against_another_members_account()
    {
        [$member] = $this->memberWithDueCharge();
        [$otherMember] = $this->memberWithDueCharge();

        $this->goCardless->expects($this->never())->method('newBill');

        $this->actingAs($member)
            ->postJson(route('account.payment.gocardless.create', $otherMember->id), [
                'reason' => 'subscription',
                'amount' => '2200',
            ])
            ->assertStatus(403);
    }

    /** @test */
    public function a_failed_payment_attempt_does_not_block_the_charge_from_nightly_billing()
    {
        [$member, $charge] = $this->memberWithDueCharge();

        $this->goCardless->method('newBill')
            ->willThrowException(new \GoCardlessPro\Core\Exception\ValidationFailedException('mandate is not active'));
        $this->goCardless->method('getNameFromReason')->willReturn('Monthly subscription');

        $this->actingAs($member)
            ->postJson(route('account.payment.gocardless.create', $member->id), [
                'reason' => 'subscription',
                'amount' => '2200',
            ])
            ->assertStatus(400);

        // The failure is recorded, but not linked to the charge - a linked payment
        // would make the nightly biller skip the charge forever
        $failedPayment = Payment::where('user_id', $member->id)->first();
        $this->assertNotNull($failedPayment);
        $this->assertEquals('failed', $failedPayment->status);
        $this->assertEmpty($failedPayment->reference);

        $charge->refresh();
        $this->assertEquals('due', $charge->status);
    }
}
