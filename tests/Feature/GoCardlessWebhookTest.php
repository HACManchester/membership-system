<?php

namespace Tests\Feature;

use BB\Entities\Payment;
use BB\Entities\SubscriptionCharge;
use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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

        // The failed/cancelled path re-fetches the payment from the GoCardless API
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

    private function activeUserWithCharge(string $chargeStatus)
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'payment_method' => 'gocardless-variable',
            'subscription_expires' => Carbon::now(),
        ]);

        $charge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'payment_date' => null,
            'amount' => 1700,
            'status' => $chargeStatus,
        ]);

        $payment = factory(Payment::class)->create([
            'user_id' => $user->id,
            'reason' => 'subscription',
            'source' => 'gocardless-variable',
            'source_id' => 'PM_TEST_123',
            'reference' => $charge->id,
            'status' => 'pending',
        ]);

        return [$user, $charge, $payment];
    }

    /** @test */
    public function a_webhook_with_an_invalid_signature_is_rejected()
    {
        [, $charge, $payment] = $this->activeUserWithCharge('processing');

        $response = $this->postWebhook(
            [$this->paymentEvent('confirmed', 'PM_TEST_123')],
            'the-wrong-secret'
        );

        $response->assertStatus(403);
        $this->assertEquals('pending', $payment->fresh()->status);
        $this->assertEquals('processing', $charge->fresh()->status);
    }

    /** @test */
    public function a_webhook_with_no_signature_is_rejected()
    {
        $body = json_encode(['events' => [$this->paymentEvent('confirmed', 'PM_TEST_123')]]);

        $response = $this->call('POST', '/gocardless/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $body);

        $response->assertStatus(403);
    }

    /** @test */
    public function a_submitted_payment_event_marks_the_local_payment_pending()
    {
        [, , $payment] = $this->activeUserWithCharge('processing');
        $payment->update(['status' => 'pending_submission']);

        $this->postWebhook([$this->paymentEvent('submitted', 'PM_TEST_123')])
            ->assertStatus(200);

        $this->assertEquals('pending', $payment->fresh()->status);
    }

    /** @test */
    public function a_confirmed_payment_marks_the_payment_and_charge_paid_and_extends_membership()
    {
        [$user, $charge, $payment] = $this->activeUserWithCharge('processing');

        $this->postWebhook([$this->paymentEvent('confirmed', 'PM_TEST_123')])
            ->assertStatus(200);

        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertNotNull($payment->paid_at);

        $charge->refresh();
        $this->assertEquals('paid', $charge->status);
        $this->assertTrue($charge->payment_date->isSameDay(Carbon::now()));

        $user->refresh();
        $this->assertEquals('active', $user->status);
        $this->assertTrue($user->subscription_expires->isSameDay(Carbon::now()->addMonth()));
    }

    /** @test */
    public function a_failed_payment_cancels_the_charge_and_puts_the_member_into_payment_warning()
    {
        [$user, $charge, $payment] = $this->activeUserWithCharge('due');

        $this->goCardless->method('getPayment')
            ->willReturn((object) ['status' => 'failed']);

        $this->postWebhook([$this->paymentEvent('failed', 'PM_TEST_123')])
            ->assertStatus(200);

        $this->assertEquals('failed', $payment->fresh()->status);

        $charge->refresh();
        $this->assertEquals('cancelled', $charge->status);

        $user->refresh();
        $this->assertEquals('payment-warning', $user->status);
        // Members keep access during the grace period
        $this->assertTrue((bool) $user->active);
        $this->assertTrue($user->subscription_expires->isAfter(Carbon::now()));
    }

    /** @test */
    public function a_cancelled_mandate_clears_payment_details_and_marks_the_member_leaving()
    {
        $user = factory(User::class)->create([
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
        $this->assertEquals('leaving', $user->status);
        $this->assertEmpty($user->mandate_id);
        $this->assertEmpty($user->payment_method);
    }

    /** @test */
    public function delivering_the_same_confirmed_event_twice_is_harmless()
    {
        [$user, $charge, $payment] = $this->activeUserWithCharge('processing');

        $this->postWebhook([$this->paymentEvent('confirmed', 'PM_TEST_123')])
            ->assertStatus(200);
        $expiryAfterFirst = $user->fresh()->subscription_expires;

        $this->postWebhook([$this->paymentEvent('confirmed', 'PM_TEST_123')])
            ->assertStatus(200);

        $this->assertEquals('paid', $payment->fresh()->status);
        $this->assertEquals('paid', $charge->fresh()->status);
        $this->assertTrue($user->fresh()->subscription_expires->isSameDay($expiryAfterFirst));
        $this->assertEquals(1, Payment::where('source_id', 'PM_TEST_123')->count());
    }

    /** @test */
    public function an_event_for_an_unknown_payment_is_acknowledged_without_changes()
    {
        $this->postWebhook([$this->paymentEvent('confirmed', 'PM_UNKNOWN')])
            ->assertStatus(200);

        $this->assertEquals(0, Payment::count());
    }
}
