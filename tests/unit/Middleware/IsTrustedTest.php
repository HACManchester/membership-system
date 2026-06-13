<?php

namespace Tests\Unit\Middleware;

use BB\Entities\User;
use BB\Exceptions\AuthenticationException;
use BB\Http\Middleware\IsTrusted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Pins the current behaviour of the IsTrusted middleware: access requires a
 * logged-in, non-banned user who is either an admin or flagged trusted.
 */
class IsTrustedTest extends TestCase
{
    use RefreshDatabase;

    private function runMiddleware($ajax = false)
    {
        $request = Request::create('/test', 'GET', [], [], [], $ajax
            ? ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
            : []);
        $this->app->instance('request', $request);

        return (new IsTrusted())->handle($request, function () {
            return 'PASSED';
        });
    }

    /** @test */
    public function a_guest_is_redirected_to_login()
    {
        $result = $this->runMiddleware();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringEndsWith('/login', $result->getTargetUrl());
    }

    /** @test */
    public function a_guest_making_an_ajax_request_gets_a_401()
    {
        $result = $this->runMiddleware($ajax = true);

        $this->assertEquals(401, $result->getStatusCode());
    }

    /** @test */
    public function a_trusted_user_passes()
    {
        $this->actingAs(factory(User::class)->create(['trusted' => true]));

        $this->assertEquals('PASSED', $this->runMiddleware());
    }

    /** @test */
    public function an_admin_passes_even_when_not_flagged_trusted()
    {
        $this->actingAs(factory(User::class)->state('admin')->create(['trusted' => false]));

        $this->assertEquals('PASSED', $this->runMiddleware());
    }

    /** @test */
    public function an_untrusted_non_admin_is_rejected()
    {
        $this->actingAs(factory(User::class)->create(['trusted' => false]));

        $this->expectException(AuthenticationException::class);
        $this->runMiddleware();
    }

    /** @test */
    public function a_banned_user_is_rejected_even_when_trusted()
    {
        $this->actingAs(factory(User::class)->create(['trusted' => true, 'banned' => true]));

        $this->expectException(AuthenticationException::class);
        $this->runMiddleware();
    }
}
