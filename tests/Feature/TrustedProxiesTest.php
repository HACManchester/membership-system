<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The app runs behind an HTTPS proxy (OrbStack locally, nginx in production).
 * URLs it generates must use the scheme and host the proxy forwarded, or
 * browsers block http:// assets and redirects from the https:// page, but
 * only a configured proxy may say so.
 */
class TrustedProxiesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_test/generated-url', fn () => url('/somewhere'));
    }

    #[Test]
    public function urls_use_the_scheme_and_host_a_trusted_proxy_forwarded()
    {
        // phpunit.xml sets TRUSTED_PROXIES=10.0.0.1
        $this->forwardedThrough('10.0.0.1')
            ->assertSeeText('https://members.example.test/somewhere');
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
            ->assertSeeText('https://members.example.test/somewhere');
    }

    private function forwardedThrough(string $proxyAddress): \Illuminate\Testing\TestResponse
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $proxyAddress])
            ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'members.example.test'])
            // A full URL, so the result doesn't depend on APP_URL
            ->get('http://app.internal/_test/generated-url');
    }
}
