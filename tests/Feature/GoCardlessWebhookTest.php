<?php

namespace Tests\Feature;

use BB\Entities\Payment;
use BB\Entities\SubscriptionCharge;
use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use BB\Support\MemberStatus;
use BB\Support\PaymentStatus;
use BB\Support\SubscriptionChargeStatus;
use Carbon\Carbon;
use GoCardlessPro\Resources\Payment as GoCardlessPayment;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

// The GoCardless mock is shared by every test. Some set expectations on it,
// others only stub it, so the stub-only tests mustn't raise a PHPUnit notice
#[AllowMockObjectsWithoutExpectations]
class GoCardlessWebhookTest extends TestCase
{
    use RefreshDatabase;

    const WEBHOOK_SECRET = 'test-webhook-secret';

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $goCardless;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.gocardless.webhook_secret' => self::WEBHOOK_SECRET]);

        // Failed/cancelled payments and legacy subscription payments re-fetch the payment
        // from the GoCardless API; everything else is driven by the webhook alone
        $this->goCardless = $this->createMock(GoCardlessHelper::class);
        $this->app->instance(GoCardlessHelper::class, $this->goCardless);
    }

    private function postWebhook(array $events, string $secret = self::WEBHOOK_SECRET)
    {
        $body = json_encode(['events' => $events]);
        $signature = hash_hmac('sha256', $body, $secret);

        return $this->call('POST', '/gocardless/webhook', [], [], [], [
            'HTTP_WEBHOOK_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $body);
    }

    private function paymentEvent(string $action, string $paymentId)
    {
        return [
            'resource_type' => 'payments',
            'action' => $action,
            'links' => ['payment' => $paymentId],
        ];
    }

    private function subscriptionEvent(string $action, string $subscriptionId, ?string $paymentId = null)
    {
        $links = ['subscription' => $subscriptionId];
        if ($paymentId) {
            $links['payment'] = $paymentId;
        }

        return [
            'resource_type' => 'subscriptions',
            'action' => $action,
            'links' => $links,
        ];
    }

    /**
     * A real SDK resource rather than a bare object: its magic getter throws on any
     * property the API doesn't have, so a misspelt or removed field in the
     * controller fails here instead of in production.
     */
    private function gocardlessPayment(string $id, int $amountInPence, string $status)
    {
        return new GoCardlessPayment(json_decode(json_encode([
            'id' => $id,
            'amount' => $amountInPence,
            'currency' => 'GBP',
            'status' => $status,
            'charge_date' => Carbon::now()->toDateString(),
            'links' => ['mandate' => 'MD_TEST_123'],
        ])));
    }

    private function activeUserWithCharge(string $chargeStatus, string $paymentId = 'PM_TEST_123')
    {
        $user = User::factory()->create([
            'status' => 'active',
            'active' => true,
            'payment_method' => 'gocardless-variable',
            'subscription_expires' => Carbon::now(),
        ]);

        $charge = SubscriptionCharge::factory()->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'payment_date' => null,
            'amount' => 1700,
            'status' => $chargeStatus,
        ]);

        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'reason' => 'subscription',
            'source' => 'gocardless-variable',
            'source_id' => $paymentId,
            'reference' => $charge->id,
            'status' => 'pending',
        ]);

        return [$user, $charge, $payment];
    }

    #[Test]
    public function a_webhook_with_an_invalid_signature_is_rejected()
    {
        [, $charge, $payment] = $this->activeUserWithCharge('processing');

        $response = $this->postWebhook(
            [$this->paymentEvent('confirmed', 'PM_TEST_123')],
            'the-wrong-secret'
        );

        $response->assertStatus(403);
        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
        $this->assertSame(SubscriptionChargeStatus::Processing, $charge->fresh()->status);
    }

    #[Test]
    public function a_webhook_with_no_signature_is_rejected()
    {
        $body = json_encode(['events' => [$this->paymentEvent('confirmed', 'PM_TEST_123')]]);

        $response = $this->call('POST', '/gocardless/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $body);

        $response->assertStatus(403);
    }

    #[Test]
    public function a_submitted_payment_event_marks_the_local_payment_pending()
    {
        [, , $payment] = $this->activeUserWithCharge('processing');
        $payment->update(['status' => 'pending_submission']);

        $this->postWebhook([$this->paymentEvent('submitted', 'PM_TEST_123')])
            ->assertStatus(200);

        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
    }

    #[Test]
    public function a_confirmed_payment_marks_the_payment_and_charge_paid_and_extends_membership()
    {
        [$user, $charge, $payment] = $this->activeUserWithCharge('processing');

        $this->postWebhook([$this->paymentEvent('confirmed', 'PM_TEST_123')])
            ->assertStatus(200);

        $payment->refresh();
        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertNotNull($payment->paid_at);

        $charge->refresh();
        $this->assertSame(SubscriptionChargeStatus::Paid, $charge->status);
        $this->assertTrue($charge->payment_date->isSameDay(Carbon::now()));

        $user->refresh();
        $this->assertSame(MemberStatus::Active, $user->status);
        $this->assertTrue($user->subscription_expires->isSameDay(Carbon::now()->addMonth()));
    }

    #[Test]
    public function a_failed_payment_cancels_the_charge_and_puts_the_member_into_payment_warning()
    {
        [$user, $charge, $payment] = $this->activeUserWithCharge('due');

        $this->goCardless->method('getPayment')
            ->willReturn($this->gocardlessPayment('PM_TEST_123', 1700, 'failed'));

        $this->postWebhook([$this->paymentEvent('failed', 'PM_TEST_123')])
            ->assertStatus(200);

        $this->assertSame(PaymentStatus::Failed, $payment->fresh()->status);

        $charge->refresh();
        $this->assertSame(SubscriptionChargeStatus::Cancelled, $charge->status);

        $user->refresh();
        $this->assertSame(MemberStatus::PaymentWarning, $user->status);
        // Members keep access during the grace period
        $this->assertTrue((bool) $user->active);
        $this->assertTrue($user->subscription_expires->isAfter(Carbon::now()));
    }

    #[Test]
    public function a_cancelled_mandate_clears_payment_details_and_marks_the_member_leaving()
    {
        $user = User::factory()->create([
            'status' => 'active',
            'active' => true,
            'payment_method' => 'gocardless-variable',
            'mandate_id' => 'MD_TEST_123',
        ]);

        $this->postWebhook([[
            'resource_type' => 'mandates',
            'action' => 'cancelled',
            'links' => ['mandate' => 'MD_TEST_123'],
        ]])->assertStatus(200);

        $user->refresh();
        $this->assertSame(MemberStatus::Leaving, $user->status);
        $this->assertEmpty($user->mandate_id);
        $this->assertEmpty($user->payment_method);
    }

    #[Test]
    public function a_cancelled_mandate_cancels_the_outstanding_charges_it_leaves_behind()
    {
        $user = User::factory()->create([
            'status' => 'active',
            'active' => true,
            'payment_method' => 'gocardless-variable',
            'mandate_id' => 'MD_TEST_123',
        ]);

        $pending = SubscriptionCharge::factory()->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now()->addDays(7),
            'status' => 'pending',
        ]);

        $due = SubscriptionCharge::factory()->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'status' => 'due',
        ]);

        $this->postWebhook([[
            'resource_type' => 'mandates',
            'action' => 'cancelled',
            'links' => ['mandate' => 'MD_TEST_123'],
        ]])->assertStatus(200);

        // Left outstanding these are uncollectable, and get billed in a batch against
        // whatever mandate the member sets up when they rejoin
        $this->assertSame(SubscriptionChargeStatus::Cancelled, $pending->fresh()->status);
        $this->assertSame(SubscriptionChargeStatus::Cancelled, $due->fresh()->status);
    }

    #[Test]
    public function delivering_the_same_confirmed_event_twice_is_harmless()
    {
        [$user, $charge, $payment] = $this->activeUserWithCharge('processing');

        $this->postWebhook([$this->paymentEvent('confirmed', 'PM_TEST_123')])
            ->assertStatus(200);
        $expiryAfterFirst = $user->fresh()->subscription_expires;

        $this->postWebhook([$this->paymentEvent('confirmed', 'PM_TEST_123')])
            ->assertStatus(200);

        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertSame(SubscriptionChargeStatus::Paid, $charge->fresh()->status);
        $this->assertTrue($user->fresh()->subscription_expires->isSameDay($expiryAfterFirst));
        $this->assertEquals(1, Payment::where('source_id', 'PM_TEST_123')->count());
    }

    #[Test]
    public function an_event_for_an_unknown_payment_is_acknowledged_without_changes()
    {
        $this->postWebhook([$this->paymentEvent('confirmed', 'PM_UNKNOWN')])
            ->assertStatus(200);

        $this->assertEquals(0, Payment::count());
    }

    /**
     * A member on a legacy fixed-amount GoCardless subscription. The nightly biller
     * skips them; their charge is settled when GoCardless's own subscription takes a
     * payment and tells us via `subscriptions.payment_created`.
     */
    private function legacyMemberWithCharge(int $chargeAmount = 2200)
    {
        $user = User::factory()->create([
            'status' => 'active',
            'active' => true,
            'payment_method' => 'gocardless',
            'subscription_id' => 'SB_LEGACY_1',
            'mandate_id' => 'MD_LEGACY_1',
            'subscription_expires' => Carbon::now(),
        ]);

        $charge = SubscriptionCharge::factory()->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'payment_date' => null,
            'amount' => $chargeAmount,
            'status' => 'due',
        ]);

        return [$user, $charge];
    }

    // ---------------------------------------------------------------------
    // Legacy fixed-amount subscriptions (payment_method = 'gocardless')
    // ---------------------------------------------------------------------
    #[Test]
    public function a_legacy_subscription_payment_is_recorded_and_linked_to_the_members_charge()
    {
        [$user, $charge] = $this->legacyMemberWithCharge();

        $this->goCardless->expects($this->once())
            ->method('getPayment')
            ->with('PM_SUB_1')
            ->willReturn($this->gocardlessPayment('PM_SUB_1', 2200, 'pending_submission'));

        $this->postWebhook([$this->subscriptionEvent('payment_created', 'SB_LEGACY_1', 'PM_SUB_1')])
            ->assertStatus(200);

        $payment = Payment::where('source_id', 'PM_SUB_1')->firstOrFail();
        $this->assertEquals($user->id, $payment->user_id);
        $this->assertEquals('subscription', $payment->reason);
        $this->assertEquals('gocardless', $payment->source);
        $this->assertSame(2200, $payment->amount, 'the API reports pence, as payments.amount is');
        $this->assertSame(PaymentStatus::PendingSubmission, $payment->status);
        $this->assertEquals((string) $charge->id, $payment->reference);

        // Not collected yet, so the charge and membership are untouched
        $this->assertSame(SubscriptionChargeStatus::Due, $charge->fresh()->status);
        $this->assertTrue($user->fresh()->subscription_expires->isSameDay(Carbon::now()));
    }

    #[Test]
    public function a_legacy_charge_takes_the_amount_actually_collected()
    {
        [, $charge] = $this->legacyMemberWithCharge(1700);

        $this->goCardless->method('getPayment')
            ->willReturn($this->gocardlessPayment('PM_SUB_1', 2200, 'pending_submission'));

        $this->postWebhook([$this->subscriptionEvent('payment_created', 'SB_LEGACY_1', 'PM_SUB_1')])
            ->assertStatus(200);

        $this->assertSame(2200, $charge->fresh()->amount);
    }

    #[Test]
    public function a_legacy_subscription_payment_that_is_confirmed_pays_the_charge_and_extends_membership()
    {
        [$user, $charge] = $this->legacyMemberWithCharge();

        $this->goCardless->method('getPayment')
            ->willReturn($this->gocardlessPayment('PM_SUB_1', 2200, 'pending_submission'));

        $this->postWebhook([$this->subscriptionEvent('payment_created', 'SB_LEGACY_1', 'PM_SUB_1')])->assertStatus(200);
        $this->postWebhook([$this->paymentEvent('submitted', 'PM_SUB_1')])->assertStatus(200);
        $this->assertSame(PaymentStatus::Pending, Payment::where('source_id', 'PM_SUB_1')->first()->status);

        $this->postWebhook([$this->paymentEvent('confirmed', 'PM_SUB_1')])->assertStatus(200);

        $this->assertSame(PaymentStatus::Paid, Payment::where('source_id', 'PM_SUB_1')->first()->status);
        $this->assertSame(SubscriptionChargeStatus::Paid, $charge->fresh()->status);

        $user->refresh();
        $this->assertSame(MemberStatus::Active, $user->status);
        $this->assertTrue($user->subscription_expires->isSameDay(Carbon::now()->addMonth()));
    }

    #[Test]
    public function a_legacy_subscription_payment_that_fails_puts_the_member_into_payment_warning()
    {
        [$user, $charge] = $this->legacyMemberWithCharge();

        $this->goCardless->method('getPayment')->willReturnOnConsecutiveCalls(
            $this->gocardlessPayment('PM_SUB_1', 2200, 'pending_submission'),
            $this->gocardlessPayment('PM_SUB_1', 2200, 'failed')
        );

        $this->postWebhook([$this->subscriptionEvent('payment_created', 'SB_LEGACY_1', 'PM_SUB_1')])->assertStatus(200);
        $this->postWebhook([$this->paymentEvent('failed', 'PM_SUB_1')])->assertStatus(200);

        $this->assertSame(PaymentStatus::Failed, Payment::where('source_id', 'PM_SUB_1')->first()->status);
        $this->assertSame(SubscriptionChargeStatus::Cancelled, $charge->fresh()->status);
        $this->assertSame(MemberStatus::PaymentWarning, $user->fresh()->status);
    }

    #[Test]
    public function a_redelivered_legacy_subscription_payment_is_recorded_once()
    {
        [, $charge] = $this->legacyMemberWithCharge();

        $this->goCardless->method('getPayment')
            ->willReturn($this->gocardlessPayment('PM_SUB_1', 2200, 'pending_submission'));

        $event = $this->subscriptionEvent('payment_created', 'SB_LEGACY_1', 'PM_SUB_1');
        $this->postWebhook([$event])->assertStatus(200);
        $this->postWebhook([$event])->assertStatus(200);

        $this->assertEquals(1, Payment::where('source_id', 'PM_SUB_1')->count());
        $this->assertEquals((string) $charge->id, Payment::where('source_id', 'PM_SUB_1')->first()->reference);
    }

    #[Test]
    public function a_subscription_payment_for_an_unknown_subscription_is_acknowledged_without_recording_anything()
    {
        $this->goCardless->method('getPayment')
            ->willReturn($this->gocardlessPayment('PM_SUB_1', 2200, 'pending_submission'));

        $this->postWebhook([$this->subscriptionEvent('payment_created', 'SB_UNKNOWN', 'PM_SUB_1')])
            ->assertStatus(200);

        $this->assertEquals(0, Payment::count());
    }

    // ---------------------------------------------------------------------
    // Subscription cancellation
    // ---------------------------------------------------------------------
    #[Test]
    public function a_cancelled_legacy_subscription_marks_the_member_leaving_and_cancels_their_charges()
    {
        [$user, $charge] = $this->legacyMemberWithCharge();

        $this->postWebhook([$this->subscriptionEvent('cancelled', 'SB_LEGACY_1')])
            ->assertStatus(200);

        $user->refresh();
        $this->assertSame(MemberStatus::Leaving, $user->status);
        $this->assertEmpty($user->payment_method);
        $this->assertEmpty($user->subscription_id);
        $this->assertEmpty($user->mandate_id);
        $this->assertSame(SubscriptionChargeStatus::Cancelled, $charge->fresh()->status);
    }

    /**
     * A member who moved to variable billing can still have an old subscription
     * id on file. Its cancellation must not touch the mandate they now pay through.
     */
    #[Test]
    public function a_cancelled_old_subscription_on_a_variable_member_only_clears_the_subscription()
    {
        [$user, $charge] = $this->activeUserWithCharge('due');
        // Neither field is mass-assignable, so update() would silently drop them
        $user->forceFill(['subscription_id' => 'SB_OLD_1', 'mandate_id' => 'MD_TEST_123'])->save();
        $this->assertEquals('SB_OLD_1', $user->fresh()->subscription_id);

        $this->postWebhook([$this->subscriptionEvent('cancelled', 'SB_OLD_1')])
            ->assertStatus(200);

        $user->refresh();
        $this->assertSame(MemberStatus::Active, $user->status);
        $this->assertEquals('gocardless-variable', $user->payment_method);
        $this->assertEquals('MD_TEST_123', $user->mandate_id);
        $this->assertEmpty($user->subscription_id);
        $this->assertSame(SubscriptionChargeStatus::Due, $charge->fresh()->status);
    }

    // ---------------------------------------------------------------------
    // Cancelled payments
    // ---------------------------------------------------------------------
    #[Test]
    public function a_cancelled_payment_is_handled_like_a_failed_one()
    {
        [$user, $charge, $payment] = $this->activeUserWithCharge('due');

        $this->goCardless->method('getPayment')
            ->willReturn($this->gocardlessPayment('PM_TEST_123', 1700, 'cancelled'));

        $this->postWebhook([$this->paymentEvent('cancelled', 'PM_TEST_123')])
            ->assertStatus(200);

        $this->assertSame(PaymentStatus::Cancelled, $payment->fresh()->status);
        $this->assertSame(SubscriptionChargeStatus::Cancelled, $charge->fresh()->status);
        $this->assertSame(MemberStatus::PaymentWarning, $user->fresh()->status);
    }

    // ---------------------------------------------------------------------
    // Batched and redelivered webhooks
    // ---------------------------------------------------------------------
    /**
     * GoCardless batches events into one delivery; every test above sends one.
     */
    #[Test]
    public function every_event_in_a_multi_event_delivery_is_processed()
    {
        [, , $submitted] = $this->activeUserWithCharge('processing', 'PM_A');
        $submitted->update(['status' => 'pending_submission']);
        [$confirmedUser, $confirmedCharge, $confirmed] = $this->activeUserWithCharge('processing', 'PM_B');
        $leaving = User::factory()->create([
            'status' => 'active', 'active' => true,
            'payment_method' => 'gocardless-variable', 'mandate_id' => 'MD_LEAVING',
        ]);

        $this->postWebhook([
            $this->paymentEvent('submitted', 'PM_A'),
            $this->paymentEvent('confirmed', 'PM_B'),
            ['resource_type' => 'mandates', 'action' => 'cancelled', 'links' => ['mandate' => 'MD_LEAVING']],
        ])->assertStatus(200);

        $this->assertSame(PaymentStatus::Pending, $submitted->fresh()->status);
        $this->assertSame(PaymentStatus::Paid, $confirmed->fresh()->status);
        $this->assertSame(SubscriptionChargeStatus::Paid, $confirmedCharge->fresh()->status);
        $this->assertTrue($confirmedUser->fresh()->subscription_expires->isSameDay(Carbon::now()->addMonth()));
        $this->assertSame(MemberStatus::Leaving, $leaving->fresh()->status);
    }

    #[Test]
    public function redelivering_a_submitted_event_is_harmless()
    {
        [, $charge, $payment] = $this->activeUserWithCharge('processing');
        $payment->update(['status' => 'pending_submission']);

        $this->postWebhook([$this->paymentEvent('submitted', 'PM_TEST_123')])->assertStatus(200);
        $this->postWebhook([$this->paymentEvent('submitted', 'PM_TEST_123')])->assertStatus(200);

        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
        $this->assertSame(SubscriptionChargeStatus::Processing, $charge->fresh()->status);
        $this->assertEquals(1, Payment::where('source_id', 'PM_TEST_123')->count());
    }

    #[Test]
    public function redelivering_a_failed_event_is_harmless()
    {
        [$user, $charge, $payment] = $this->activeUserWithCharge('due');

        $this->goCardless->method('getPayment')
            ->willReturn($this->gocardlessPayment('PM_TEST_123', 1700, 'failed'));

        $this->postWebhook([$this->paymentEvent('failed', 'PM_TEST_123')])->assertStatus(200);
        $afterFirst = $user->fresh();

        $this->postWebhook([$this->paymentEvent('failed', 'PM_TEST_123')])->assertStatus(200);

        $user->refresh();
        $this->assertSame(PaymentStatus::Failed, $payment->fresh()->status);
        $this->assertSame(SubscriptionChargeStatus::Cancelled, $charge->fresh()->status);
        $this->assertSame(MemberStatus::PaymentWarning, $user->status);
        $this->assertTrue($user->subscription_expires->equalTo($afterFirst->subscription_expires));
    }

    /**
     * If a GoCardless API call fails part-way through a delivery, the webhook
     * errors and GoCardless redelivers the whole batch — so the events that had
     * already been applied are applied again. The end state must be the same as
     * if the first delivery had succeeded: in particular, membership is extended
     * once, not twice.
     */
    #[Test]
    public function a_delivery_that_fails_part_way_through_is_safe_to_redeliver()
    {
        [$paidUser, $paidCharge, $paid] = $this->activeUserWithCharge('processing', 'PM_A');
        [$failedUser, $failedCharge, $failed] = $this->activeUserWithCharge('due', 'PM_B');

        $delivery = [
            $this->paymentEvent('confirmed', 'PM_A'),
            $this->paymentEvent('failed', 'PM_B'),
        ];

        $this->goCardless->method('getPayment')->willReturnOnConsecutiveCalls(
            $this->throwException(new ConnectException('Connection timed out', new Request('GET', '/payments/PM_B'))),
            $this->gocardlessPayment('PM_B', 1700, 'failed')
        );

        // First attempt: the API call for the second event fails
        $first = $this->postWebhook($delivery);
        $this->assertGreaterThanOrEqual(500, $first->getStatusCode(), 'GoCardless must see a failure so it redelivers');
        $this->assertSame(PaymentStatus::Paid, $paid->fresh()->status, 'events before the failure are already applied');
        $this->assertSame(PaymentStatus::Pending, $failed->fresh()->status);

        // GoCardless redelivers the whole batch
        $this->postWebhook($delivery)->assertStatus(200);

        $this->assertSame(PaymentStatus::Paid, $paid->fresh()->status);
        $this->assertSame(SubscriptionChargeStatus::Paid, $paidCharge->fresh()->status);
        $this->assertTrue(
            $paidUser->fresh()->subscription_expires->isSameDay(Carbon::now()->addMonth()),
            'membership must be extended once, not once per delivery'
        );

        $this->assertSame(PaymentStatus::Failed, $failed->fresh()->status);
        $this->assertSame(SubscriptionChargeStatus::Cancelled, $failedCharge->fresh()->status);
        $this->assertSame(MemberStatus::PaymentWarning, $failedUser->fresh()->status);
    }
}
