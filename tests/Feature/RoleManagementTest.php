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

    private function updateRole(Role $role, array $memberIds)
    {
        return [
            'title' => $role->title,
            'description' => $role->description,
            'members' => $memberIds,
        ];
    }

    /** @test */
    public function the_roles_index_renders_for_an_admin()
    {
        $this->actingAs($this->admin())->get(route('roles.index'))
            ->assertStatus(200)
            ->assertInertia(function ($page) {
                $page->component('Roles/Index')->has('roles');
            });
    }

    /** @test */
    public function the_role_edit_page_exposes_its_members()
    {
        $role = Role::findByName('finance');
        $member = factory(User::class)->create();
        $member->assignRole($role);

        $this->actingAs($this->admin())->get(route('roles.edit', $role->id))
            ->assertInertia(function ($page) use ($role) {
                $page->component('Roles/Edit')
                    ->where('role.id', $role->id)
                    ->has('role.members', 1);
            });
    }

    /** @test */
    public function an_admin_can_assign_a_role_to_a_member()
    {
        $role = Role::findByName('finance');
        $member = factory(User::class)->create();

        $this->actingAs($this->admin())
            ->put(route('roles.update', $role->id), $this->updateRole($role, [$member->id]))
            ->assertRedirect();

        $this->assertTrue($member->fresh()->hasRole('finance'));
    }

    /** @test */
    public function an_admin_can_remove_a_role_from_a_member()
    {
        $role = Role::findByName('finance');
        $member = factory(User::class)->create();
        $member->assignRole($role);

        // Submitting the edit form with the member omitted removes them.
        $this->actingAs($this->admin())
            ->put(route('roles.update', $role->id), $this->updateRole($role, []))
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
            ->put(route('roles.update', $role->id), $this->updateRole($role, [$member->id]))
            ->assertRedirect();

        $this->assertTrue($member->fresh()->isAdmin());
    }

    /** @test */
    public function updating_a_role_persists_its_fields()
    {
        $role = Role::findByName('finance');

        $this->actingAs($this->admin())
            ->put(route('roles.update', $role->id), [
                'title' => 'Finance Team',
                'description' => 'Looks after the money',
                'email_public' => 'finance@example.com',
                'slack_channel' => 'finance',
                'members' => [],
            ])->assertRedirect();

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'title' => 'Finance Team',
            'description' => 'Looks after the money',
            'email_public' => 'finance@example.com',
            'slack_channel' => 'finance',
        ]);
    }

    /** @test */
    public function updating_a_role_with_empty_text_fields_saves_blank_not_null()
    {
        $role = Role::findByName('finance');

        $this->actingAs($this->admin())
            ->put(route('roles.update', $role->id), [
                'title' => 'Finance',
                'members' => [],
            ])->assertRedirect();

        // Omitted text fields default to '' (title is a NOT NULL column).
        $this->assertDatabaseHas('roles', ['id' => $role->id, 'title' => 'Finance', 'email_public' => '']);
    }

    /** @test */
    public function the_role_name_cannot_be_changed()
    {
        $role = Role::findByName('finance');

        $this->actingAs($this->admin())
            ->put(route('roles.update', $role->id), [
                'name' => 'hacked',
                'title' => 'Finance Team',
                'members' => [],
            ])->assertRedirect();

        $this->assertEquals('finance', $role->fresh()->name);
    }

    /** @test */
    public function a_non_admin_cannot_manage_roles()
    {
        $role = Role::findByName('finance');
        $member = factory(User::class)->create();
        $target = factory(User::class)->create();

        $this->actingAs($member)
            ->put(route('roles.update', $role->id), $this->updateRole($role, [$target->id]))
            ->assertStatus(403);

        $this->assertFalse($target->fresh()->hasRole('finance'));
    }
}
