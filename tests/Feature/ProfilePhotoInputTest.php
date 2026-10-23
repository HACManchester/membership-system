<?php

namespace Tests\Feature;

use BB\Entities\ProfileData;
use BB\Entities\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Posting new_profile_photo[] (an array) instead of a single file used to call
 * getRealPath() on an array: an Error, which the surrounding catch (\Exception)
 * doesn't catch, so the request 500'd. Only a single uploaded file is a photo.
 */
class ProfilePhotoInputTest extends TestCase
{
    /** @test */
    public function a_single_photo_is_still_uploaded_for_approval()
    {
        Storage::fake('public');
        $member = factory(User::class)->create(['status' => 'active', 'active' => true]);
        factory(ProfileData::class)->create(['user_id' => $member->id, 'new_profile_photo' => false]);

        $this->actingAs($member)
            ->put(route('account.profile.update', $member->id), [
                'tagline' => 'With a photo',
                'new_profile_photo' => UploadedFile::fake()->image('me.jpg', 800, 800),
            ])
            ->assertRedirect();

        $this->assertTrue((bool) ProfileData::where('user_id', $member->id)->first()->new_profile_photo);
        // UserImage names files by an md5 of the member's hash
        $name = md5($member->hash);
        [$width] = getimagesizefromstring(Storage::disk('public')->get("user-photo/{$name}-new.png"));
        [$thumbWidth] = getimagesizefromstring(Storage::disk('public')->get("user-photo/{$name}-thumb-new.png"));
        $this->assertSame([500, 200], [$width, $thumbWidth]);
    }

    /** @test */
    public function a_profile_update_with_an_array_of_photos_is_handled_without_error()
    {
        $member = factory(User::class)->create(['status' => 'active', 'active' => true]);
        factory(ProfileData::class)->create(['user_id' => $member->id, 'new_profile_photo' => false]);

        $this->actingAs($member)
            ->put(route('account.profile.update', $member->id), [
                'tagline' => 'Still saved',
                'new_profile_photo' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
            ])
            ->assertRedirect();

        $profile = ProfileData::where('user_id', $member->id)->first();
        $this->assertSame('Still saved', $profile->tagline);
        $this->assertFalse((bool) $profile->new_profile_photo, 'no photo should be recorded');
    }

    /** @test */
    public function a_registration_with_an_array_of_photos_is_handled_without_error()
    {
        $this->post(route('account.store'), [
            'given_name' => 'Grace',
            'family_name' => 'Hopper',
            'email' => 'grace@example.test',
            'display_name' => 'grace',
            'online_only' => '0',
            'suppress_real_name' => '0',
            'password' => 'a-long-enough-password',
            'phone' => '07700900123',
            'address' => ['line_1' => '1 Test Street', 'postcode' => 'M4 7HR'],
            'emergency_contact' => 'Someone Else',
            'monthly_subscription' => config('membership.prices.minimum'),
            'rules_agreed' => '1',
            'new_profile_photo' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ])->assertRedirect();

        $user = User::where('email', 'grace@example.test')->firstOrFail();
        $this->assertFalse((bool) $user->profile->new_profile_photo, 'no photo should be recorded');
    }
}
