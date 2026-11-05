<?php

namespace Tests\Feature;

use BB\Entities\Payment;
use BB\Entities\SubscriptionCharge;
use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use BB\Repo\PaymentRepository;
use BB\Services\MemberSubscriptionCharges;
use BB\Support\PaymentStatus;
use BB\Support\SubscriptionChargeStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Money is in pence: a £22.50 member is charged, billed and paid 2250 pence,
 * and nothing on the way loses the 50p.
 */
class SubscriptionAmountWithPenceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_member_paying_pounds_and_pence_is_charged_and_billed_the_exact_amount()
    {
        Carbon::setTestNow('2026-11-10 09:00:00');

        $member = User::factory()->create([
            'status' => 'active',
            'active' => true,
            'payment_method' => 'gocardless-variable',
            'mandate_id' => 'MD123',
            'payment_day' => 10,
            'monthly_subscription' => 2250,
        ]);

        $goCardless = $this->createMock(GoCardlessHelper::class);
        $goCardless->method('getNameFromReason')->willReturn('Monthly subscription');
        $goCardless->expects($this->once())
            ->method('newBill')
            ->with('MD123', $this->identicalTo(2250), 'Monthly subscription')
            ->willReturn((object) ['id' => 'PM123', 'status' => 'pending_submission']);
        $this->app->instance(GoCardlessHelper::class, $goCardless);

        $charges = app(MemberSubscriptionCharges::class);
        $charges->createSubscriptionCharges(Carbon::today());
        $charges->makeChargesDue();
        $charges->billMembers();

        $charge = SubscriptionCharge::where('user_id', $member->id)->sole();
        $payment = Payment::where('user_id', $member->id)->sole();
        $this->assertSame(2250, $charge->amount);
        $this->assertSame(SubscriptionChargeStatus::Processing, $charge->status);
        $this->assertSame(2250, $payment->amount);
        $this->assertSame((string) $charge->id, (string) $payment->reference);

        app(PaymentRepository::class)->markPaymentPaid($payment->id, Carbon::now());

        $charge->refresh();
        $this->assertSame(SubscriptionChargeStatus::Paid, $charge->status);
        $this->assertSame(2250, $charge->amount);
        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertSame(2250, $payment->fresh()->amount);
    }
}
