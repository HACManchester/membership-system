<?php

namespace Tests\Feature;

use BB\Entities\Role;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin()
    {
        $user = factory(User::class)->create();
        $user->assignRole(Role::findByName('admin'));

        return $user;
    }

    /** @test */
    public function an_admin_can_assign_a_role_to_a_member()
    {
        $role = Role::findByName('finance');
        $member = factory(User::class)->create();

        $this->actingAs($this->admin())
            ->post(route('roles.users.store', $role->id), ['user_id' => $member->id])
            ->assertRedirect();

        $this->assertTrue($member->fresh()->hasRole('finance'));
    }

    /** @test */
    public function an_admin_can_remove_a_role_from_a_member()
    {
        $role = Role::findByName('finance');
        $member = factory(User::class)->create();
        $member->assignRole($role);

        $this->actingAs($this->admin())
            ->delete(route('roles.users.destroy', [$role->id, $member->id]))
            ->assertRedirect();

        $this->assertFalse($member->fresh()->hasRole('finance'));
    }

    /**
     * @test
     * Pins current behaviour: any admin can grant the admin role itself, with no second-party
     * approval. Flagged in the access-control review as a privilege-escalation consideration.
     */
    public function an_admin_can_grant_the_admin_role()
    {
        $role = Role::findByName('admin');
        $member = factory(User::class)->create();

        $this->actingAs($this->admin())
            ->post(route('roles.users.store', $role->id), ['user_id' => $member->id])
            ->assertRedirect();

        $this->assertTrue($member->fresh()->isAdmin());
    }

    /** @test */
    public function a_non_admin_cannot_assign_roles()
    {
        $role = Role::findByName('finance');
        $member = factory(User::class)->create();
        $target = factory(User::class)->create();

        $this->actingAs($member)
            ->post(route('roles.users.store', $role->id), ['user_id' => $target->id])
            ->assertStatus(403);

        $this->assertFalse($target->fresh()->hasRole('finance'));
    }
}
