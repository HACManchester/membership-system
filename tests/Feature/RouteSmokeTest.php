<?php

namespace Tests\Feature;

use BB\Entities\Course;
use BB\Entities\Equipment;
use BB\Entities\EquipmentArea;
use BB\Entities\KeyFob;
use BB\Entities\MaintainerGroup;
use BB\Entities\Role;
use BB\Entities\Room;
use BB\Entities\User;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Requests every GET route as each persona and fails on any 5xx.
 *
 * This is deliberately shallow: authorization specifics belong in the dedicated
 * tests (KeyFobTest, RoleManagementTest, DisciplinaryTest, ...). What this pins
 * is that no route *explodes* — the failure mode a framework or PHP upgrade
 * actually produces. A removed Blade directive, a renamed view binding or a
 * changed mail API shows up here as a 500 on a page nothing else covers.
 *
 * New routes are picked up automatically; no list to maintain.
 */
class RouteSmokeTest extends TestCase
{
    /**
     * Routes that cannot be smoke-tested through the web guard.
     *
     * Keep this list short and justified — an entry here is a hole in the net.
     */
    private const SKIPPED_URIS = [
        // Authenticated by the `api` token guard, not the web session; covered
        // byte-for-byte in KeyFobCsvTest.
        'api/keyfobs/csv',
        // Framework-owned broadcasting auth endpoint.
        'broadcasting/auth',
        // Third-party controller that reads real files out of storage/logs;
        // what it renders depends on the machine, not on our code.
        'logs',
        // Expects a signed Discourse SSO payload; meaningless without one.
        'sso/login',
    ];

    /** @var array<string,mixed> */
    private $world = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->world = $this->seedWorld();
    }

    /** @test */
    public function no_get_route_returns_a_server_error_for_a_guest()
    {
        $this->assertNoServerErrors(null);
    }

    /** @test */
    public function no_get_route_returns_a_server_error_for_a_member()
    {
        $this->assertNoServerErrors($this->world['member']);
    }

    /** @test */
    public function no_get_route_returns_a_server_error_for_a_trusted_member()
    {
        $this->assertNoServerErrors($this->world['trusted']);
    }

    /** @test */
    public function no_get_route_returns_a_server_error_for_an_admin()
    {
        $this->assertNoServerErrors($this->world['admin']);
    }

    /**
     * Walks every smokeable GET route, collecting *all* failures rather than
     * stopping at the first — after an upgrade you want the whole list.
     *
     * @param User|null $user null for a guest
     */
    private function assertNoServerErrors($user)
    {
        $failures = [];

        foreach ($this->smokeableGetRoutes() as $route) {
            $uri = $this->resolveUri($route);

            $request = $user ? $this->actingAs($user) : $this;

            try {
                $status = $request->get($uri)->getStatusCode();
            } catch (\Throwable $e) {
                $failures[] = sprintf(
                    '%s threw %s: %s',
                    $uri,
                    get_class($e),
                    $e->getMessage()
                );
                continue;
            }

            if ($status >= 500) {
                $failures[] = sprintf('%s returned %d', $uri, $status);
            }
        }

        $this->assertSame([], $failures, sprintf(
            "%d GET route(s) failed as %s:\n  - %s\n",
            count($failures),
            $user ? 'a logged-in user' : 'a guest',
            implode("\n  - ", $failures)
        ));
    }

    /**
     * @return RoutingRoute[]
     */
    private function smokeableGetRoutes()
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            if (in_array($route->uri(), self::SKIPPED_URIS, true)) {
                continue;
            }

            $routes[] = $route;
        }

        return $routes;
    }

    /**
     * Substitutes a real, resolvable value for every route parameter so that
     * detail pages exercise their view instead of 404ing on a missing model.
     */
    private function resolveUri(RoutingRoute $route)
    {
        $uri = $route->uri();

        foreach ($route->parameterNames() as $name) {
            $value = $this->parameterValue($name);

            $uri = str_replace(['{' . $name . '}', '{' . $name . '?}'], $value, $uri);
        }

        return '/' . ltrim($uri, '/');
    }

    /**
     * @param  string $name
     * @return string
     */
    private function parameterValue($name)
    {
        $w = $this->world;

        // Models with a getRouteKeyName() of 'slug' must be addressed by slug.
        $map = [
            'account'          => $w['member']->id,
            'user'             => $w['member']->id,
            'member'           => $w['member']->id,
            'id'               => $w['member']->id,
            'hash'             => $w['member']->hash,
            'keyfob'           => $w['keyfob']->id,
            'role'             => $w['role']->id,
            'course'           => $w['course']->slug,
            'equipment'        => $w['equipment']->slug,
            'tool_id'          => $w['equipment']->slug,
            'equipment_area'   => $w['equipmentArea']->slug,
            'maintainer_group' => $w['maintainerGroup']->slug,
            'room'             => $w['room']->slug,
            'status'           => 'working',
            'token'            => 'smoke-test-token',
        ];

        if (! array_key_exists($name, $map)) {
            $this->fail(sprintf(
                'RouteSmokeTest has no fixture for route parameter {%s}. '
                . 'Add one to parameterValue() so the new route is covered.',
                $name
            ));
        }

        return (string) $map[$name];
    }

    /**
     * @return array<string,mixed>
     */
    private function seedWorld()
    {
        $member = factory(User::class)->create();
        factory(\BB\Entities\ProfileData::class)->create(['user_id' => $member->id]);

        $trusted = factory(User::class)->create(['trusted' => true]);
        factory(\BB\Entities\ProfileData::class)->create(['user_id' => $trusted->id]);

        $admin = factory(User::class)->state('admin')->create();
        factory(\BB\Entities\ProfileData::class)->create(['user_id' => $admin->id]);

        return [
            'member'          => $member,
            'trusted'         => $trusted,
            'admin'           => $admin,
            'keyfob'          => factory(KeyFob::class)->create(['user_id' => $member->id]),
            'role'            => Role::where('name', 'admin')->firstOrFail(),
            'course'          => factory(Course::class)->create(),
            'equipment'       => factory(Equipment::class)->create(),
            'equipmentArea'   => factory(EquipmentArea::class)->create(),
            'maintainerGroup' => factory(MaintainerGroup::class)->create(),
            'room'            => factory(Room::class)->create(),
        ];
    }
}
