<?php

namespace Tests\Feature;

use BB\Entities\KeyFob;
use BB\Entities\ProfileData;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccountAccessTest extends TestCase
{
    use RefreshDatabase;

    private function memberWithProfile(array $attributes = [])
    {
        $user = User::factory()->create($attributes);
        ProfileData::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    #[Test]
    public function a_member_can_view_their_own_account_page()
    {
        $user = $this->memberWithProfile();

        $this->actingAs($user)
            ->get("/account/{$user->id}")
            ->assertStatus(200)
            ->assertSee($user->name)
            ->assertSee($user->email);
    }

    #[Test]
    public function the_account_page_counts_the_members_active_access_methods()
    {
        $user = $this->memberWithProfile();
        KeyFob::factory()->count(2)->for($user)->create();
        KeyFob::factory()->for($user)->create(['active' => false]);

        $this->actingAs($user)
            ->get("/account/{$user->id}")
            ->assertOk()
            ->assertSee('2 access methods');
    }

    #[Test]
    public function the_account_page_offers_to_set_up_an_access_method_when_none_is_active()
    {
        $user = $this->memberWithProfile();
        KeyFob::factory()->for($user)->create(['active' => false]);

        $this->actingAs($user)
            ->get("/account/{$user->id}")
            ->assertOk()
            ->assertSee('Set up an access method');
    }

    #[Test]
    public function a_member_cannot_view_another_members_account_page()
    {
        $user = $this->memberWithProfile();
        $other = $this->memberWithProfile();

        $this->actingAs($user)->get("/account/{$other->id}")->assertStatus(403);
    }

    #[Test]
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

    #[Test]
    public function guests_are_redirected_away_from_the_members_directory()
    {
        $this->memberWithProfile();

        $this->get('/members')->assertStatus(302);
    }

    #[Test]
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

    #[Test]
    public function an_inactive_members_profile_is_not_viewable()
    {
        $user = $this->memberWithProfile();
        $inactive = $this->memberWithProfile(['active' => false]);

        $this->actingAs($user)
            ->get("/members/{$inactive->id}")
            ->assertRedirect(route('members.index'));
    }

    #[Test]
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
