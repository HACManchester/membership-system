<?php

use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use GoCardlessPro\Core\Exception\ApiException;
use GoCardlessPro\Core\Exception\PermissionsException;
use GoCardlessPro\Core\Exception\ValidationFailedException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Drives GoCardlessHelper against the real GoCardless *sandbox* API.
 *
 * Everywhere else in the suite GoCardlessHelper is mocked, so these are the only
 * tests that exercise the SDK itself — the thing an SDK upgrade can break. They
 * are opt-in: without GOCARDLESS_SANDBOX_ACCESS_TOKEN they skip, so CI and normal
 * runs never touch the network.
 *
 *   docker compose exec -e GOCARDLESS_SANDBOX_ACCESS_TOKEN=sandbox_... laravel \
 *       vendor/bin/phpunit --group gocardless-sandbox
 *
 * A dedicated variable (rather than GOCARDLESS_ACCESS_TOKEN) and a forced SANDBOX
 * environment mean a live token can't be used by accident.
 *
 * Mandates: by default a throwaway customer, bank account and mandate are
 * created through the API. Some GoCardless accounts aren't permitted to create
 * mandates directly; in that case set GOCARDLESS_SANDBOX_MANDATE_ID to a sandbox
 * mandate you set up once through the hosted page. A mandate you supply is
 * never cancelled by these tests.
 *
 * Redirect-flow *completion* can't be automated — it needs a person on the
 * hosted page — so it's covered by asserting the real API error for completing
 * an unvisited flow.
 */
#[Group('gocardless-sandbox')]
class GoCardlessSandboxTest extends TestCase
{
    /** @var string|null cached across tests so only one mandate is made per run */
    private static $mandateId;

    /** @var bool whether $mandateId was created here (and so may be cancelled) */
    private static $mandateIsOurs = false;

    /** @var GoCardlessHelper */
    private $helper;

    protected function setUp(): void
    {
        parent::setUp();

        $token = getenv('GOCARDLESS_SANDBOX_ACCESS_TOKEN');
        if (! $token) {
            $this->markTestSkipped('Set GOCARDLESS_SANDBOX_ACCESS_TOKEN to run the GoCardless sandbox tests.');
        }

        config([
            'services.gocardless.access_token' => $token,
            'services.gocardless.environment' => 'SANDBOX',
        ]);

        $this->helper = new GoCardlessHelper();
    }

    #[Test]
    public function a_redirect_flow_is_created_and_its_id_stored_on_the_member()
    {
        $user = factory(User::class)->create(['given_name' => 'Sandbox', 'family_name' => 'Member']);

        $url = $this->helper->newPreAuthUrl($user, [
            'description' => 'Hackspace Manchester',
            'success_redirect_url' => 'https://example.test/return',
            'session_token' => 'user-token-' . $user->id,
            'prefilled_customer' => ['given_name' => 'Sandbox', 'family_name' => 'Member', 'country_code' => 'GB'],
        ]);

        $this->assertStringStartsWith('https://pay-sandbox.gocardless.com/', $url);
        $this->assertStringStartsWith('RE', $user->fresh()->gocardless_setup_id);
    }

    /**
     * The hosted page can't be completed without a person, so assert the next
     * best thing: completing an unvisited flow fails with a real API exception
     * the controller's catch block will handle.
     */
    #[Test]
    public function completing_an_unvisited_redirect_flow_fails_with_an_api_exception()
    {
        $user = factory(User::class)->create();
        $this->helper->newPreAuthUrl($user, [
            'description' => 'Hackspace Manchester',
            'success_redirect_url' => 'https://example.test/return',
            'session_token' => 'user-token-' . $user->id,
        ]);

        try {
            $this->helper->confirmResource($user->fresh(), []);
            $this->fail('Completing an unvisited redirect flow should not succeed.');
        } catch (ApiException $e) {
            $this->assertNotSame('', $e->getMessage());
        }
    }

    #[Test]
    public function a_payment_is_created_fetched_and_cancelled()
    {
        $payment = $this->helper->newBill($this->mandate(), 100, 'Sandbox test payment');

        $this->assertStringStartsWith('PM', $payment->id);
        $this->assertSame(100, $payment->amount);
        $this->assertSame('GBP', $payment->currency);

        $fetched = $this->helper->getPayment($payment->id);
        $this->assertSame($payment->id, $fetched->id);
        $this->assertSame($this->mandate(), $fetched->links->mandate);

        $cancelled = $this->helper->cancelPayment($payment->id);
        $this->assertSame('cancelled', $cancelled->status);
    }

    /**
     * Billing sends a key identifying the subscription charge, so that billing a
     * charge twice can't collect twice. GoCardless must answer the repeat with
     * the payment it already created, and the SDK must hand that back.
     */
    #[Test]
    public function a_repeated_idempotency_key_returns_the_original_payment()
    {
        $key = 'sandbox-test-' . bin2hex(random_bytes(8));

        $first = $this->helper->newBill($this->mandate(), 100, 'Sandbox idempotency test', idempotencyKey: $key);
        $repeat = $this->helper->newBill($this->mandate(), 100, 'Sandbox idempotency test', idempotencyKey: $key);

        $this->assertStringStartsWith('PM', $first->id);
        $this->assertSame($first->id, $repeat->id);

        $this->helper->cancelPayment($first->id);
    }

    /**
     * The regression SDK 1.7.0 hid: a validation failure carrying field-level
     * errors went through a legacy implode() that is a TypeError on PHP 8,
     * which the billing code's catch blocks don't catch. It must arrive as a
     * ValidationFailedException with a readable message.
     */
    #[Test]
    public function an_invalid_payment_raises_a_validation_failed_exception_with_its_field_errors()
    {
        try {
            $this->helper->newBill($this->mandate(), 0, 'Sandbox zero-amount payment');
            $this->fail('A zero-amount payment should be rejected.');
        } catch (ValidationFailedException $e) {
            $this->assertNotEmpty($e->getErrors());
            $this->assertNotSame('', $e->getMessage());
        }
    }

    #[Test]
    public function a_subscription_is_created_and_cancelled()
    {
        $subscription = $this->helper->createSubscription($this->mandate(), 100, 15, 'sandbox-test');

        $this->assertStringStartsWith('SB', $subscription->id);
        $this->assertSame(100, $subscription->amount);
        $this->assertSame('monthly', $subscription->interval_unit);
        $this->assertSame('sandbox-test', $subscription->metadata->subscription_number);

        $cancelled = $this->helper->cancelSubscription($subscription->id);
        $this->assertSame('cancelled', $cancelled->status);
    }

    /**
     * Runs last (alphabetically and by declaration) and only on a mandate these
     * tests created — never on one supplied through GOCARDLESS_SANDBOX_MANDATE_ID.
     */
    #[Test]
    public function zz_a_mandate_is_cancelled()
    {
        $mandateId = $this->mandate();

        if (! self::$mandateIsOurs) {
            $this->markTestSkipped('Not cancelling a mandate supplied via GOCARDLESS_SANDBOX_MANDATE_ID.');
        }

        $this->assertTrue($this->helper->cancelPreAuth($mandateId));

        self::$mandateId = null;
        self::$mandateIsOurs = false;
    }

    /**
     * A sandbox mandate to bill against: the one supplied, or one made here.
     */
    private function mandate()
    {
        if (self::$mandateId) {
            return self::$mandateId;
        }

        if ($supplied = getenv('GOCARDLESS_SANDBOX_MANDATE_ID')) {
            return self::$mandateId = $supplied;
        }

        // Fixture set-up goes straight to the SDK: the app never creates
        // customers or mandates itself (members do that on the hosted page).
        $client = new \GoCardlessPro\Client([
            'access_token' => config('services.gocardless.access_token'),
            'environment' => \GoCardlessPro\Environment::SANDBOX,
        ]);

        try {
            $customer = $client->customers()->create(['params' => [
                'email' => 'sandbox-' . uniqid() . '@example.test',
                'given_name' => 'Sandbox',
                'family_name' => 'Test',
                'address_line1' => '1 Test Street',
                'city' => 'Manchester',
                'postal_code' => 'M1 1AA',
                'country_code' => 'GB',
            ]]);

            // GoCardless's documented sandbox test account.
            $bankAccount = $client->customerBankAccounts()->create(['params' => [
                'account_holder_name' => 'Sandbox Test',
                'account_number' => '55779911',
                'branch_code' => '200000',
                'country_code' => 'GB',
                'links' => ['customer' => $customer->id],
            ]]);

            $mandate = $client->mandates()->create(['params' => [
                'scheme' => 'bacs',
                'links' => ['customer_bank_account' => $bankAccount->id],
            ]]);
        } catch (PermissionsException $e) {
            $this->markTestSkipped(
                'This GoCardless account can\'t create mandates through the API. Complete a sandbox '
                . 'redirect flow once and set GOCARDLESS_SANDBOX_MANDATE_ID to the mandate it produces.'
            );
        }

        self::$mandateIsOurs = true;

        return self::$mandateId = $mandate->id;
    }
}
