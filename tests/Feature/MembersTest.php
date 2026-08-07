<?php

namespace Tests\Feature;

use BB\Entities\ProfileData;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembersTest extends TestCase
{
    use RefreshDatabase;

    private function memberWithProfile(array $attributes = []): User
    {
        $user = factory(User::class)->create($attributes);
        factory(ProfileData::class)->create(['user_id' => $user->id]);

        return $user;
    }

    public function test_index_renders_the_member_grid()
    {
        $viewer = factory(User::class)->create();
        $this->memberWithProfile();

        $this->actingAs($viewer)->get(route('members.index'))
            ->assertStatus(200)
            ->assertInertia(function ($page) {
                $page->component('Members/Index')->has('members', 1);
            });
    }

    public function test_show_renders_a_public_active_member_profile()
    {
        $viewer = factory(User::class)->create();
        $member = $this->memberWithProfile();

        $this->actingAs($viewer)->get(route('members.show', $member->id))
            ->assertStatus(200)
            ->assertInertia(function ($page) use ($member) {
                $page->component('Members/Show')
                    ->where('profile.name', $member->name)
                    ->has('profile.photo_url')
                    ->where('can.edit', false);
            });
    }

    public function test_show_marks_can_edit_for_the_member_themselves()
    {
        $member = $this->memberWithProfile();

        $this->actingAs($member)->get(route('members.show', $member->id))
            ->assertInertia(function ($page) {
                $page->component('Members/Show')->where('can.edit', true);
            });
    }

    public function test_show_redirects_when_a_non_admin_views_an_inactive_member()
    {
        $viewer = factory(User::class)->create();
        $inactive = $this->memberWithProfile(['active' => false, 'status' => 'left']);

        $this->actingAs($viewer)->get(route('members.show', $inactive->id))
            ->assertRedirect(route('members.index'));
    }

    public function test_admin_can_view_an_inactive_member()
    {
        $admin = factory(User::class)->state('admin')->create();
        $inactive = $this->memberWithProfile(['active' => false, 'status' => 'left']);

        $this->actingAs($admin)->get(route('members.show', $inactive->id))
            ->assertStatus(200)
            ->assertInertia(function ($page) {
                $page->component('Members/Show');
            });
    }
}
