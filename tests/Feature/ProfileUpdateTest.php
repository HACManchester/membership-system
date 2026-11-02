<?php

namespace Tests\Feature;

use BB\Entities\ProfileData;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Whether a member's photo is approved, and whether it goes on the wall, are
 * admin decisions: the profile form must not let a member set them.
 */
class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function memberWithProfile(): User
    {
        $member = User::factory()->create();
        ProfileData::factory()->create([
            'user_id' => $member->id,
            'profile_photo' => false,
            'profile_photo_on_wall' => false,
        ]);

        return $member;
    }

    #[Test]
    public function a_member_cannot_approve_their_own_photo_or_put_it_on_the_wall()
    {
        $member = $this->memberWithProfile();

        $this->actingAs($member)
            ->put(route('account.profile.update', $member->id), [
                'tagline' => 'Maker of things',
                'profile_photo' => 1,
                'profile_photo_on_wall' => 1,
            ])
            ->assertRedirect(route('members.show', $member->id));

        $this->assertDatabaseHas('profile_data', [
            'user_id' => $member->id,
            'tagline' => 'Maker of things',
            'profile_photo' => false,
            'profile_photo_on_wall' => false,
        ]);
    }

    #[Test]
    public function an_admin_can_edit_another_members_profile()
    {
        $member = $this->memberWithProfile();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('account.profile.update', $member->id), ['tagline' => 'Edited by an admin'])
            ->assertRedirect(route('members.show', $member->id));

        $this->assertDatabaseHas('profile_data', ['user_id' => $member->id, 'tagline' => 'Edited by an admin']);
    }

    #[Test]
    public function a_member_cannot_edit_another_members_profile()
    {
        $member = $this->memberWithProfile();

        $this->actingAs($this->memberWithProfile())
            ->put(route('account.profile.update', $member->id), ['tagline' => 'Not mine'])
            ->assertForbidden();

        $this->assertDatabaseMissing('profile_data', ['tagline' => 'Not mine']);
    }

    #[Test]
    public function an_invalid_website_is_rejected()
    {
        $member = $this->memberWithProfile();

        $this->actingAs($member)
            ->from(route('account.profile.edit', $member->id))
            ->put(route('account.profile.update', $member->id), ['tagline' => 'Unsaved', 'website' => 'not a url'])
            ->assertRedirect(route('account.profile.edit', $member->id))
            ->assertSessionHasErrors(['website' => 'The website must be a valid URL.']);

        $this->assertDatabaseMissing('profile_data', ['tagline' => 'Unsaved']);
    }

    #[Test]
    public function a_rejected_website_shows_its_error_under_the_website_field()
    {
        $member = $this->memberWithProfile();

        $this->actingAs($member)
            ->from(route('account.profile.edit', $member->id))
            ->followingRedirects()
            ->put(route('account.profile.update', $member->id), ['website' => 'not a url'])
            ->assertOk()
            ->assertSee('<span class="help-block">The website must be a valid URL.</span>', false);
    }
}
