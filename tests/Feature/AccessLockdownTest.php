<?php

namespace Tests\Feature;

use BB\Entities\AccessLockdown;
use BB\Entities\User;
use Tests\TestCase;

class AccessLockdownTest extends TestCase
{
    /** @test */
    public function an_admin_can_view_the_lockdown_page()
    {
        $admin = factory(User::class)->state('admin')->create();

        $response = $this->actingAs($admin)->get(route('access-lockdown.index'));

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('Admin/AccessLockdown/Index')->where('lockdown', null);
        });
    }

    /**
     * `committee` is configured as a default but doesn't exist in every environment.
     * Offering it pre-selected with no checkbox to clear would fail validation.
     *
     * @test
     */
    public function the_default_roles_are_narrowed_to_roles_that_exist()
    {
        $admin = factory(User::class)->state('admin')->create();

        $response = $this->actingAs($admin)->get(route('access-lockdown.index'));

        $response->assertInertia(function ($page) {
            $page->where('defaultRoles', ['admin', 'board']);
        });
    }

    /** @test */
    public function an_admin_can_start_a_lockdown()
    {
        $admin = factory(User::class)->state('admin')->create();

        $response = $this->actingAs($admin)->post(route('access-lockdown.store'), [
            'reason' => 'Burst pipe in the wood shop',
            'roles' => ['admin', 'board'],
        ]);

        $response->assertRedirect(route('access-lockdown.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('access_lockdowns', [
            'started_by' => $admin->id,
            'reason' => 'Burst pipe in the wood shop',
            'lifted_at' => null,
        ]);

        $this->assertEquals(['admin', 'board'], AccessLockdown::current()->roles);
    }

    /** @test */
    public function a_second_lockdown_cannot_be_started_while_one_is_running()
    {
        $admin = factory(User::class)->state('admin')->create();

        $this->actingAs($admin)->post(route('access-lockdown.store'), [
            'reason' => 'First',
            'roles' => ['admin'],
        ]);

        $this->actingAs($admin)->post(route('access-lockdown.store'), [
            'reason' => 'Second',
            'roles' => ['board'],
        ]);

        $this->assertEquals(1, AccessLockdown::count());
        $this->assertEquals('First', AccessLockdown::current()->reason);
    }

    /** @test */
    public function a_lockdown_must_name_at_least_one_role()
    {
        $admin = factory(User::class)->state('admin')->create();

        $response = $this->actingAs($admin)->post(route('access-lockdown.store'), [
            'reason' => 'Nobody at all',
            'roles' => [],
        ]);

        $response->assertSessionHasErrors('roles');
        $this->assertEquals(0, AccessLockdown::count());
    }

    /** @test */
    public function a_lockdown_cannot_name_a_role_that_does_not_exist()
    {
        $admin = factory(User::class)->state('admin')->create();

        $response = $this->actingAs($admin)->post(route('access-lockdown.store'), [
            'roles' => ['not-a-real-role'],
        ]);

        $response->assertSessionHasErrors('roles.0');
        $this->assertEquals(0, AccessLockdown::count());
    }

    /** @test */
    public function an_admin_can_lift_a_lockdown()
    {
        $admin = factory(User::class)->state('admin')->create();
        $lockdown = $this->runningLockdown($admin);

        $response = $this->actingAs($admin)->delete(route('access-lockdown.destroy'));

        $response->assertRedirect(route('access-lockdown.index'));
        $response->assertSessionHas('success');
        $this->assertNull(AccessLockdown::current());

        $lockdown->refresh();
        $this->assertNotNull($lockdown->lifted_at);
        $this->assertEquals($admin->id, $lockdown->lifted_by);
    }

    /**
     * `current()` only reads the newest row, so if duplicates ever slip through,
     * lifting must clear all of them or the space silently stays shut.
     *
     * @test
     */
    public function lifting_clears_every_active_lockdown()
    {
        $admin = factory(User::class)->state('admin')->create();
        $this->runningLockdown($admin);
        $this->runningLockdown($admin);

        $this->actingAs($admin)->delete(route('access-lockdown.destroy'));

        $this->assertNull(AccessLockdown::current());
        $this->assertEquals(0, AccessLockdown::active()->count());
    }

    /** @test */
    public function a_member_cannot_view_or_change_the_lockdown()
    {
        $admin = factory(User::class)->state('admin')->create();
        $member = factory(User::class)->create();
        $this->runningLockdown($admin);

        $this->actingAs($member)->get(route('access-lockdown.index'))->assertForbidden();
        $this->actingAs($member)->post(route('access-lockdown.store'), [
            'roles' => ['admin'],
        ])->assertForbidden();
        $this->actingAs($member)->delete(route('access-lockdown.destroy'))->assertForbidden();

        $this->assertNotNull(AccessLockdown::current());
    }

    /** @test */
    public function a_guest_is_sent_to_login()
    {
        $this->get(route('access-lockdown.index'))->assertRedirect(route('login'));
    }

    private function runningLockdown(User $admin): AccessLockdown
    {
        return AccessLockdown::create([
            'started_by' => $admin->id,
            'reason' => 'Testing',
            'roles' => ['admin'],
        ]);
    }
}
