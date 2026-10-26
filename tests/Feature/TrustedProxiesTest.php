<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The app runs behind an HTTPS proxy (OrbStack locally, nginx in production).
 * URLs it generates must use the scheme the proxy forwarded, or browsers block
 * http:// assets and redirects from the https:// page, but only a configured
 * proxy may say so. Nothing else is taken from forwarded headers.
 */
class TrustedProxiesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_test/generated-url', fn () => url('/somewhere'));
        Route::get('/_test/client-ip', fn () => request()->ip());
    }

    #[Test]
    public function urls_use_the_scheme_a_trusted_proxy_forwarded()
    {
        // phpunit.xml sets TRUSTED_PROXIES=10.0.0.1
        $this->forwardedThrough('10.0.0.1')
            ->assertSeeText('https://app.internal/somewhere');
    }

    #[Test]
    public function a_forwarded_host_is_ignored_even_from_a_trusted_proxy()
    {
        // The production proxy passes a client-sent X-Forwarded-Host through,
        // which would otherwise put an attacker's domain in password-reset links
        $this->forwardedThrough('10.0.0.1')
            ->assertDontSeeText('members.example.test');
    }

    #[Test]
    public function a_forwarded_client_ip_is_ignored_even_from_a_trusted_proxy()
    {
        // Production's Apache has already set REMOTE_ADDR to the client, so
        // whatever X-Forwarded-For is left over came from the client
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->withHeaders(['X-Forwarded-For' => '203.0.113.7'])
            ->get('http://app.internal/_test/client-ip')
            ->assertContent('10.0.0.1');
    }

    #[Test]
    public function an_untrusted_proxys_forwarded_headers_are_ignored()
    {
        $this->forwardedThrough('10.0.0.99')
            ->assertSeeText('http://app.internal/somewhere');
    }

    #[Test]
    public function no_proxy_is_trusted_when_none_is_configured()
    {
        config(['trustedproxy.proxies' => null]);

        $this->forwardedThrough('10.0.0.1')
            ->assertSeeText('http://app.internal/somewhere');
    }

    #[Test]
    public function a_wildcard_trusts_whichever_proxy_connects()
    {
        config(['trustedproxy.proxies' => '*']);

        $this->forwardedThrough('192.168.97.1')
            ->assertSeeText('https://app.internal/somewhere');
    }

    private function forwardedThrough(string $proxyAddress): \Illuminate\Testing\TestResponse
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $proxyAddress])
            ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'members.example.test'])
            // A full URL, so the result doesn't depend on APP_URL
            ->get('http://app.internal/_test/generated-url');
    }
}
