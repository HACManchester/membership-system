<?php

namespace Tests\Unit\Middleware;

use BB\Entities\User;
use BB\Exceptions\AuthenticationException;
use BB\Http\Middleware\HasRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Pins the *current* behaviour of the HasRole middleware before any refactor.
 *
 * The headline quirk being documented: role:member does NOT check membership.
 * The role check is skipped entirely when the required role is 'member', so the
 * middleware only requires "logged in and not banned" for those routes.
 */
class HasRoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Run the middleware and return whatever it produced. The sentinel is
     * returned only if the request was allowed through to the next handler.
     */
    private function runMiddleware($role = 'guest', $ajax = false)
    {
        $request = Request::create('/test', 'GET', [], [], [], $ajax
            ? ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
            : []);
        // The middleware reads the current request via the \Request facade,
        // so bind the one we built into the container too.
        $this->app->instance('request', $request);

        return (new HasRole())->handle($request, function () {
            return 'PASSED';
        }, $role);
    }

    /** @test */
    public function a_guest_is_redirected_to_login()
    {
        $result = $this->runMiddleware('member');

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringEndsWith('/login', $result->getTargetUrl());
    }

    /** @test */
    public function a_guest_making_an_ajax_request_gets_a_401()
    {
        $result = $this->runMiddleware('member', $ajax = true);

        $this->assertEquals(401, $result->getStatusCode());
    }

    /** @test */
    public function a_user_with_the_required_role_passes()
    {
        $this->actingAs(factory(User::class)->state('admin')->create());

        $this->assertEquals('PASSED', $this->runMiddleware('admin'));
    }

    /** @test */
    public function a_user_without_the_required_role_is_rejected()
    {
        $this->actingAs(factory(User::class)->create());

        $this->expectException(AuthenticationException::class);
        $this->runMiddleware('admin');
    }

    /**
     * @test
     * Documents the quirk: role:member only requires a logged-in, non-banned
     * account. A user with no roles at all still passes.
     */
    public function role_member_lets_any_logged_in_user_through_regardless_of_roles()
    {
        $this->actingAs(factory(User::class)->create());

        $this->assertEquals('PASSED', $this->runMiddleware('member'));
    }

    /** @test */
    public function a_banned_user_is_rejected_even_for_a_member_route()
    {
        $this->actingAs(factory(User::class)->create(['banned' => true]));

        $this->expectException(AuthenticationException::class);
        $this->runMiddleware('member');
    }

    /** @test */
    public function a_banned_user_who_has_the_required_role_is_still_rejected()
    {
        $this->actingAs(factory(User::class)->state('admin')->create(['banned' => true]));

        $this->expectException(AuthenticationException::class);
        $this->runMiddleware('admin');
    }
}
