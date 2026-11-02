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
        $user = User::factory()->create($attributes);
        ProfileData::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    public function test_index_renders_the_member_grid()
    {
        $viewer = User::factory()->create();
        $this->memberWithProfile();

        $this->actingAs($viewer)->get(route('members.index'))
            ->assertStatus(200)
            ->assertInertia(function ($page) {
                $page->component('Members/Index')->has('members', 1);
            });
    }

    public function test_show_renders_a_public_active_member_profile()
    {
        $viewer = User::factory()->create();
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

    public function test_show_links_the_member_social_profiles()
    {
        $viewer = User::factory()->create();
        $member = User::factory()->create();
        ProfileData::factory()->create([
            'user_id' => $member->id,
            'github' => 'octocat',
            'twitter' => 'hacman',
            'google_plus' => 'hacman_tg',
            'facebook' => 'a&b',
        ]);

        $this->actingAs($viewer)->get(route('members.show', $member->id))
            ->assertInertia(function ($page) {
                $page->where('profile.links.github', 'https://github.com/octocat')
                    ->where('profile.links.twitter', 'https://twitter.com/hacman')
                    ->where('profile.links.telegram', 'https://t.me/hacman_tg')
                    ->where('profile.links.facebook', 'https://www.facebook.com/a&amp;b');
            });
    }

    public function test_show_has_no_social_links_for_a_member_without_handles()
    {
        $viewer = User::factory()->create();
        $member = $this->memberWithProfile();

        $this->actingAs($viewer)->get(route('members.show', $member->id))
            ->assertInertia(function ($page) {
                $page->where('profile.links.github', null)
                    ->where('profile.links.twitter', null)
                    ->where('profile.links.telegram', null)
                    ->where('profile.links.facebook', null);
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
        $viewer = User::factory()->create();
        $inactive = $this->memberWithProfile(['active' => false, 'status' => 'left']);

        $this->actingAs($viewer)->get(route('members.show', $inactive->id))
            ->assertRedirect(route('members.index'));
    }

    public function test_admin_can_view_an_inactive_member()
    {
        $admin = User::factory()->admin()->create();
        $inactive = $this->memberWithProfile(['active' => false, 'status' => 'left']);

        $this->actingAs($admin)->get(route('members.show', $inactive->id))
            ->assertStatus(200)
            ->assertInertia(function ($page) {
                $page->component('Members/Show');
            });
    }
}
