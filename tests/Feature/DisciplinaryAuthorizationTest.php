<?php

namespace Tests\Feature;

use BB\Entities\Role;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisciplinaryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function admin()
    {
        $user = factory(User::class)->create();
        $user->assignRole(Role::findByName('admin'));

        return $user;
    }

    /** @test */
    public function a_non_admin_cannot_ban_a_member()
    {
        $member = factory(User::class)->create();
        $target = factory(User::class)->create();

        $this->actingAs($member)
            ->post(route('disciplinary.ban', $target), ['reason' => 'because'])
            ->assertStatus(403);

        $this->assertFalse((bool) $target->fresh()->banned);
    }

    /** @test */
    public function an_admin_cannot_ban_themselves()
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('disciplinary.ban', $admin), ['reason' => 'oops'])
            ->assertStatus(403);

        $this->assertFalse((bool) $admin->fresh()->banned);
    }

    /** @test */
    public function a_non_admin_cannot_unban_a_member()
    {
        $member = factory(User::class)->create();
        $banned = factory(User::class)->create(['banned' => true, 'active' => false, 'status' => 'left']);

        $this->actingAs($member)
            ->post(route('disciplinary.unban', $banned), [])
            ->assertStatus(403);

        $this->assertTrue((bool) $banned->fresh()->banned);
    }
}
