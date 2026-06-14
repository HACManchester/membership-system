<?php

namespace Tests\Feature;

use BB\Entities\ProfileData;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountAccessTest extends TestCase
{
    use RefreshDatabase;

    private function memberWithProfile(array $attributes = [])
    {
        $user = factory(User::class)->create($attributes);
        factory(ProfileData::class)->create(['user_id' => $user->id]);

        return $user;
    }

    /** @test */
    public function a_member_can_view_their_own_account_page()
    {
        $user = $this->memberWithProfile();

        $this->actingAs($user)
            ->get("/account/{$user->id}")
            ->assertStatus(200)
            ->assertSee($user->name)
            ->assertSee($user->email);
    }

    /** @test */
    public function a_member_cannot_view_another_members_account_page()
    {
        $user = $this->memberWithProfile();
        $other = $this->memberWithProfile();

        $this->actingAs($user)->get("/account/{$other->id}")->assertStatus(403);
    }

    /** @test */
    public function the_members_directory_lists_active_members()
    {
        $user = $this->memberWithProfile();
        $other = $this->memberWithProfile();

        $this->actingAs($user)
            ->get('/members')
            ->assertStatus(200)
            ->assertSee($user->name)
            ->assertSee($other->name);
    }

    /** @test */
    public function guests_are_redirected_away_from_the_members_directory()
    {
        $this->memberWithProfile();

        $this->get('/members')->assertStatus(302);
    }

    /** @test */
    public function private_profiles_are_hidden_from_the_members_directory()
    {
        $user = $this->memberWithProfile();
        $private = $this->memberWithProfile(['profile_private' => true]);

        $this->actingAs($user)
            ->get('/members')
            ->assertStatus(200)
            ->assertSee($user->name)
            ->assertDontSee($private->name);
    }

    /** @test */
    public function an_inactive_members_profile_is_not_viewable()
    {
        $user = $this->memberWithProfile();
        $inactive = $this->memberWithProfile(['active' => false]);

        $this->actingAs($user)
            ->get("/members/{$inactive->id}")
            ->assertRedirect(route('members.index'));
    }

    /** @test */
    public function a_member_can_edit_their_profile()
    {
        $user = $this->memberWithProfile();

        $this->actingAs($user)
            ->get("/account/{$user->id}/profile/edit")
            ->assertStatus(200)
            ->assertSee('Fill in your profile');

        $this->actingAs($user)
            ->put("/account/{$user->id}/profile", ['tagline' => 'Maker of things'])
            ->assertRedirect();

        $this->assertDatabaseHas('profile_data', [
            'user_id' => $user->id,
            'tagline' => 'Maker of things',
        ]);
    }
}
