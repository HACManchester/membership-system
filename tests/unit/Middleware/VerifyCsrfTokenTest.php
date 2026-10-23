<?php

namespace Tests\Unit\Middleware;

use BB\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Laravel skips CSRF checks under PHPUnit, so the feature tests never exercise
 * them. This runs the middleware as it runs in production. The GoCardless
 * webhook must stay the only exempt route: GoCardless can't send a token, and
 * the webhook verifies its own signature instead.
 */
class VerifyCsrfTokenTest extends TestCase
{
    #[Test]
    public function the_gocardless_webhook_needs_no_token()
    {
        $this->assertSame('passed', $this->handle(Request::create('/gocardless/webhook', 'POST')));
    }

    #[Test]
    public function any_other_post_without_a_token_is_refused()
    {
        $this->expectException(TokenMismatchException::class);

        $this->handle(Request::create('/account/1/keyfobs', 'POST'));
    }

    #[Test]
    public function a_post_with_the_session_token_is_accepted()
    {
        $request = Request::create('/account/1/keyfobs', 'POST', ['_token' => 'the-token']);

        $this->assertSame('passed', $this->handle($request, 'the-token'));
    }

    private function handle(Request $request, string $sessionToken = 'another-token'): string
    {
        $session = $this->app['session']->driver();
        $session->put('_token', $sessionToken);
        $request->setLaravelSession($session);

        $middleware = new class($this->app, $this->app['encrypter']) extends VerifyCsrfToken {
            protected function runningUnitTests()
            {
                return false;
            }
        };

        return $middleware->handle($request, fn () => new Response('passed'))->getContent();
    }
}
