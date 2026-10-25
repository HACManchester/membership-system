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
            'http_client' => new Client(['handler' => $stack, 'base_uri' => 'https://api-sandbox.gocardless.com']),
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
}
