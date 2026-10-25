<?php

use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoCardlessHelperTest extends TestCase
{
    private MockHandler $responses;

    /** @var list<array{request: \Psr\Http\Message\RequestInterface}> */
    private array $sent = [];

    private GoCardlessHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();

        // The real SDK client, with its HTTP layer swapped for a recorder, so the
        // tests see exactly what the helper would send to GoCardless
        $this->responses = new MockHandler();
        $stack = HandlerStack::create($this->responses);
        $stack->push(Middleware::history($this->sent));

        $this->helper = new GoCardlessHelper();
        (new ReflectionProperty(GoCardlessHelper::class, 'client'))->setValue($this->helper, new \GoCardlessPro\Client([
            'access_token' => 'sandbox_test',
            'environment' => \GoCardlessPro\Environment::SANDBOX,
            // As the SDK configures its own client: it reads error responses itself
            'http_client' => new Client(['handler' => $stack, 'base_uri' => 'https://api-sandbox.gocardless.com', 'http_errors' => false]),
        ]));
    }

    public static function callsWithAnEmptyId(): array
    {
        return [
            'get a payment with an empty ID' => [fn (GoCardlessHelper $h) => $h->getPayment(''), 'payment'],
            'get a payment with no ID' => [fn (GoCardlessHelper $h) => $h->getPayment(null), 'payment'],
            'cancel a payment with an empty ID' => [fn (GoCardlessHelper $h) => $h->cancelPayment(''), 'payment'],
            'cancel a subscription with an empty ID' => [fn (GoCardlessHelper $h) => $h->cancelSubscription(''), 'subscription'],
            'cancel a subscription with no ID' => [fn (GoCardlessHelper $h) => $h->cancelSubscription(null), 'subscription'],
            'complete a redirect flow the member never started' => [
                fn (GoCardlessHelper $h) => $h->confirmResource(new User(), []),
                'redirect flow',
            ],
        ];
    }

    #[Test]
    #[DataProvider('callsWithAnEmptyId')]
    public function an_empty_id_is_rejected_without_contacting_gocardless(Closure $call, string $resource)
    {
        try {
            $call($this->helper);
            $this->fail('Expected an empty ID to be rejected');
        } catch (InvalidArgumentException $e) {
            $this->assertSame("No GoCardless {$resource} ID given", $e->getMessage());
        }

        $this->assertCount(0, $this->sent);
    }

    #[Test]
    public function a_bill_carries_the_given_idempotency_key()
    {
        $this->responses->append($this->paymentResponse(201, 'PM123', 'pending_submission'));

        $this->helper->newBill('MD123', 2200, 'Monthly subscription', idempotencyKey: 'subscription-charge-1-user-2-2026-10-01-MD123');

        $this->assertSame(['subscription-charge-1-user-2-2026-10-01-MD123'], $this->sent[0]['request']->getHeader('Idempotency-Key'));
    }

    #[Test]
    public function a_bill_without_a_key_keeps_the_sdks_own_random_key()
    {
        $this->responses->append($this->paymentResponse(201, 'PM1', 'pending_submission'));
        $this->responses->append($this->paymentResponse(201, 'PM2', 'pending_submission'));

        $this->helper->newBill('MD123', 500, 'Balance top up');
        $this->helper->newBill('MD123', 500, 'Balance top up');

        $first = $this->sent[0]['request']->getHeaderLine('Idempotency-Key');
        $this->assertNotSame('', $first);
        $this->assertNotSame($first, $this->sent[1]['request']->getHeaderLine('Idempotency-Key'));
    }

    /**
     * What the key relies on: GoCardless answers a reused key with a 409
     * naming the payment it already created, and the SDK returns that payment
     * rather than an error, so billing code records it like a new one.
     */
    #[Test]
    public function a_repeated_key_returns_the_payment_gocardless_already_created()
    {
        $this->responses->append(new Response(409, ['Content-Type' => 'application/json'], json_encode(['error' => [
            'type' => 'invalid_state',
            'code' => 409,
            'message' => 'A resource has already been created with this idempotency key',
            'documentation_url' => 'https://developer.gocardless.com/api-reference#idempotent_creation_conflict',
            'request_id' => 'REQ1',
            'errors' => [[
                'reason' => 'idempotent_creation_conflict',
                'message' => 'A resource has already been created with this idempotency key',
                'links' => ['conflicting_resource_id' => 'PM123'],
            ]],
        ]])));
        $this->responses->append($this->paymentResponse(200, 'PM123', 'submitted'));

        $payment = $this->helper->newBill('MD123', 2200, 'Monthly subscription', idempotencyKey: 'subscription-charge-1-user-2-2026-10-01-MD123');

        $this->assertSame('PM123', $payment->id);
        $this->assertSame('submitted', $payment->status);
        $this->assertSame(['POST', '/payments'], [$this->sent[0]['request']->getMethod(), $this->sent[0]['request']->getUri()->getPath()]);
        $this->assertSame(['GET', '/payments/PM123'], [$this->sent[1]['request']->getMethod(), $this->sent[1]['request']->getUri()->getPath()]);
    }

    #[Test]
    public function a_subscription_with_an_id_is_cancelled_at_gocardless()
    {
        $this->responses->append(new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'subscriptions' => ['id' => 'SB123', 'status' => 'cancelled'],
        ])));

        $subscription = $this->helper->cancelSubscription('SB123');

        $this->assertSame('cancelled', $subscription->status);
        $this->assertCount(1, $this->sent);
        $this->assertSame('POST', $this->sent[0]['request']->getMethod());
        $this->assertSame('/subscriptions/SB123/actions/cancel', $this->sent[0]['request']->getUri()->getPath());
    }

    private function paymentResponse(int $status, string $id, string $paymentStatus): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode([
            'payments' => ['id' => $id, 'status' => $paymentStatus, 'amount' => 2200, 'currency' => 'GBP'],
        ]));
    }
}
