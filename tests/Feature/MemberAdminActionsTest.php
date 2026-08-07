<?php

namespace Tests\Feature;

use BB\Entities\Address;
use BB\Entities\ProfileData;
use BB\Entities\User;
use BB\Events\MemberGivenTrustedStatus;
use BB\Events\MemberPhotoWasDeclined;
use BB\Helpers\GoCardlessHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Characterises the admin member-management actions that were decomposed out of
 * AccountController::adminUpdate into dedicated controllers. Behaviour must match
 * the pre-split grab-bag endpoint.
 */
class MemberAdminActionsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return factory(User::class)->state('admin')->create();
    }

    /** @test */
    public function making_a_member_trusted_persists_and_fires_the_event()
    {
        Event::fake([MemberGivenTrustedStatus::class]);
        $member = factory(User::class)->create(['trusted' => false]);

        $this->actingAs($this->admin())
            ->put(route('account.admin-update', $member->id), ['trusted' => 1])
            ->assertRedirect(route('account.show', $member->id));

        $this->assertTrue((bool) $member->fresh()->trusted);
        Event::assertDispatched(MemberGivenTrustedStatus::class);
    }

    /** @test */
    public function membership_flags_update_and_the_ajax_form_gets_json()
    {
        $member = factory(User::class)->create(['key_holder' => false]);

        // key_holder form posts via AJAX (wantsJson) — expect a 200 JSON response.
        $this->actingAs($this->admin())
            ->putJson(route('account.admin-update', $member->id), ['key_holder' => 1])
            ->assertStatus(200);

        $this->assertTrue((bool) $member->fresh()->key_holder);

        // Membership-state fields.
        $this->actingAs($this->admin())
            ->put(route('account.admin-update', $member->id), ['status' => 'active', 'active' => 1])
            ->assertRedirect(route('account.show', $member->id));

        $this->assertEquals('active', $member->fresh()->status);
    }

    /** @test */
    public function a_non_admin_cannot_use_the_admin_update_endpoint()
    {
        $member = factory(User::class)->create();
        $other = factory(User::class)->create();

        $this->actingAs($member)
            ->put(route('account.admin-update', $other->id), ['trusted' => 1])
            ->assertStatus(403);
    }

    /** @test */
    public function approving_a_photo_marks_it_approved()
    {
        $userImage = $this->createMock(\BB\Helpers\UserImage::class);
        $userImage->expects($this->once())->method('approveNewImage');
        $this->app->instance(\BB\Helpers\UserImage::class, $userImage);

        $member = factory(User::class)->create();
        factory(ProfileData::class)->create([
            'user_id' => $member->id,
            'new_profile_photo' => true,
            'profile_photo' => false,
        ]);

        $this->actingAs($this->admin())
            ->put(route('account.photo-approval', $member->id), ['photo_approved' => 1])
            ->assertRedirect(route('account.show', $member->id));

        $this->assertDatabaseHas('profile_data', [
            'user_id' => $member->id,
            'new_profile_photo' => false,
            'profile_photo' => true,
        ]);
    }

    /** @test */
    public function declining_a_photo_fires_the_declined_event()
    {
        Event::fake([MemberPhotoWasDeclined::class]);

        $member = factory(User::class)->create();
        factory(ProfileData::class)->create(['user_id' => $member->id, 'new_profile_photo' => true]);

        $this->actingAs($this->admin())
            ->put(route('account.photo-approval', $member->id), ['photo_approved' => 0])
            ->assertRedirect(route('account.show', $member->id));

        $this->assertDatabaseHas('profile_data', ['user_id' => $member->id, 'new_profile_photo' => false]);
        Event::assertDispatched(MemberPhotoWasDeclined::class);
    }

    /** @test */
    public function approving_a_pending_address_marks_it_approved()
    {
        $member = factory(User::class)->create();
        $address = Address::create([
            'user_id' => $member->id,
            'line_1' => '1 Test Street',
            'postcode' => 'M1 1AA',
            'hash' => 'testhash',
        ]);

        $this->actingAs($this->admin())
            ->put(route('account.address-approval', $member->id), ['approve_new_address' => 'Approve'])
            ->assertRedirect(route('account.show', $member->id));

        $this->assertTrue((bool) $address->fresh()->approved);
    }

    /** @test */
    public function experimental_dd_subscription_is_recorded_then_cleared()
    {
        $goCardless = $this->createMock(GoCardlessHelper::class);
        $goCardless->method('createSubscription')->willReturn((object) ['id' => 'SUB123']);
        $this->app->instance(GoCardlessHelper::class, $goCardless);

        $member = factory(User::class)->create([
            'mandate_id' => 'MANDATE1',
            'monthly_subscription' => 5,
            'payment_day' => 1,
            'subscription_id' => null,
        ]);

        $this->actingAs($this->admin())
            ->post(route('account.subscription.experimental.store', $member->id))
            ->assertRedirect(route('account.show', $member->id));

        $this->assertEquals('SUB123', $member->fresh()->subscription_id);

        $this->actingAs($this->admin())
            ->delete(route('account.subscription.experimental.destroy', $member->id))
            ->assertRedirect(route('account.show', $member->id));

        $this->assertNull($member->fresh()->subscription_id);
    }
}
