<?php

namespace Tests\Feature;

use BB\Entities\Gift;
use BB\Entities\User;
use BB\Support\MemberStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SignupTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function the_signup_page_is_reachable()
    {
        $this->get('/register')->assertStatus(200)->assertSee('Join');
    }

    #[Test]
    public function the_online_only_signup_page_no_longer_exists()
    {
        $this->get('/online-only')->assertNotFound();
    }

    #[Test]
    public function a_new_full_member_can_register()
    {
        $given = $this->faker->firstName();
        $email = $this->faker->unique()->safeEmail();

        $this->post(route('account.store'), [
            'given_name'           => $given,
            'family_name'          => $this->faker->lastName(),
            'email'                => $email,
            'display_name'         => $this->faker->userName(),
            'suppress_real_name'   => '0',
            'password'             => $this->faker->password(10),
            'phone'                => '07700900123',
            'address'              => ['line_1' => '1 Test Street', 'postcode' => 'm4 7hr'],
            'emergency_contact'    => $this->faker->name(),
            'monthly_subscription' => config('membership.prices.minimum') / 100, // the form takes pounds
            'rules_agreed'         => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email'                => $email,
            'given_name'           => $given,
            'status'               => 'setting-up',
            'monthly_subscription' => config('membership.prices.minimum'),
        ]);
        $member = User::where('email', $email)->firstOrFail();
        $this->assertDatabaseHas('user_address', [
            'user_id'  => $member->id,
            'line_1'   => '1 Test Street',
            'postcode' => 'M4 7HR',
        ]);
        $this->assertDatabaseHas('profile_data', [
            'user_id'               => $member->id,
            'new_profile_photo'     => false,
            'profile_photo_private' => false,
        ]);
    }

    #[Test]
    public function a_signup_claiming_to_be_online_only_still_creates_a_full_member()
    {
        $email = $this->faker->unique()->safeEmail();

        $this->post(route('account.store'), [
            'given_name'           => $this->faker->firstName(),
            'family_name'          => $this->faker->lastName(),
            'email'                => $email,
            'display_name'         => $this->faker->userName(),
            'online_only'          => '1',
            'suppress_real_name'   => '0',
            'password'             => $this->faker->password(10),
            'phone'                => '07700900123',
            'address'              => ['line_1' => '1 Test Street', 'postcode' => 'M4 7HR'],
            'emergency_contact'    => $this->faker->name(),
            'monthly_subscription' => config('membership.prices.minimum'),
            'rules_agreed'         => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => $email, 'online_only' => 0]);
    }

    #[Test]
    public function a_signup_claiming_to_be_online_only_must_still_give_full_member_details()
    {
        $email = $this->faker->unique()->safeEmail();

        $this->postJson(route('account.store'), [
            'given_name'         => $this->faker->firstName(),
            'family_name'        => $this->faker->lastName(),
            'email'              => $email,
            'display_name'       => $this->faker->userName(),
            'online_only'        => '1',
            'suppress_real_name' => '0',
            'password'           => $this->faker->password(10),
            'rules_agreed'       => '1',
        ])->assertUnprocessable()
            ->assertJsonValidationErrorFor('phone', null)
            ->assertJsonValidationErrorFor('address.line_1', null)
            ->assertJsonValidationErrorFor('emergency_contact', null)
            ->assertJsonValidationErrorFor('monthly_subscription', null);

        $this->assertDatabaseMissing('users', ['email' => $email]);
    }

    #[Test]
    public function a_photo_given_at_signup_is_held_for_approval_with_its_privacy_choice()
    {
        Storage::fake('public');
        $email = $this->faker->unique()->safeEmail();

        $this->post(route('account.store'), [
            'given_name'            => $this->faker->firstName(),
            'family_name'           => $this->faker->lastName(),
            'email'                 => $email,
            'display_name'          => $this->faker->userName(),
            'suppress_real_name'    => '0',
            'password'              => $this->faker->password(10),
            'phone'                 => '07700900123',
            'address'               => ['line_1' => '1 Test Street', 'postcode' => 'M4 7HR'],
            'emergency_contact'     => $this->faker->name(),
            'monthly_subscription'  => config('membership.prices.minimum'),
            'rules_agreed'          => '1',
            'new_profile_photo'     => UploadedFile::fake()->image('me.jpg', 800, 800),
            'profile_photo_private' => '1',
        ])->assertRedirect();

        $member = User::where('email', $email)->firstOrFail();
        $this->assertDatabaseHas('profile_data', [
            'user_id'               => $member->id,
            'new_profile_photo'     => true,
            'profile_photo_private' => true,
            'profile_photo'         => false,
        ]);
    }

    #[Test]
    public function redeeming_a_gift_credits_the_member_with_the_gifts_value()
    {
        $gift = Gift::factory()->create(['code' => 'GIFT1250', 'months' => 3, 'credit' => 12.50]);
        $email = $this->faker->unique()->safeEmail();

        $this->post(route('account.store'), [
            'given_name'           => $this->faker->firstName(),
            'family_name'          => $this->faker->lastName(),
            'email'                => $email,
            'display_name'         => $this->faker->userName(),
            'suppress_real_name'   => '0',
            'password'             => $this->faker->password(10),
            'phone'                => '07700900123',
            'address'              => ['line_1' => $this->faker->streetAddress(), 'postcode' => 'M4 7HR'],
            'emergency_contact'    => $this->faker->name(),
            'monthly_subscription' => config('membership.prices.minimum'),
            'rules_agreed'         => '1',
            'gift_code'            => 'GIFT1250',
        ])->assertRedirect();

        $member = User::where('email', $email)->firstOrFail();
        $this->assertSame(1250, (int) $member->cash_balance);
        $this->assertDatabaseHas('payments', [
            'user_id'   => $member->id,
            'reason'    => 'balance',
            'source'    => 'Gift Certificate',
            'amount'    => 1250,
            'status'    => 'paid',
            'reference' => 'GIFT1250',
        ]);
        $this->assertModelMissing($gift);
    }

    #[Test]
    public function signup_ignores_fields_the_form_no_longer_sends()
    {
        $email = $this->faker->unique()->safeEmail();

        $this->post(route('account.store'), [
            'given_name'                  => $this->faker->firstName(),
            'family_name'                 => $this->faker->lastName(),
            'email'                       => $email,
            'display_name'                => $this->faker->userName(),
            'online_only'                 => '0',
            'suppress_real_name'          => '0',
            'password'                    => $this->faker->password(10),
            'phone'                       => '07700900123',
            'address'                     => ['line_1' => $this->faker->streetAddress(), 'postcode' => 'M4 7HR'],
            'emergency_contact'           => $this->faker->name(),
            'monthly_subscription'        => config('membership.prices.minimum'),
            'rules_agreed'                => '1',
            'custom_monthly_subscription' => '99',
            'visited_space'               => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => $email]);
    }

    /**
     * Guards the controller's Request::only() list. UserMassAssignmentTest covers User::$fillable itself.
     */
    #[Test]
    public function a_new_member_cannot_set_admin_managed_fields_at_signup()
    {
        $email = $this->faker->unique()->safeEmail();

        $this->post(route('account.store'), [
            'given_name'             => $this->faker->firstName(),
            'family_name'            => $this->faker->lastName(),
            'email'                  => $email,
            'display_name'           => $this->faker->userName(),
            'online_only'            => '0',
            'suppress_real_name'     => '0',
            'password'               => $this->faker->password(10),
            'phone'                  => '07700900123',
            'address'                => ['line_1' => '1 Test Street', 'postcode' => 'M4 7HR'],
            'emergency_contact'      => $this->faker->name(),
            'monthly_subscription'   => config('membership.prices.minimum'),
            'rules_agreed'           => '1',
            'status'                 => 'active',
            'active'                 => '1',
            'banned'                 => '1',
            'banned_reason'          => 'self-inflicted',
            'banned_date'            => '2026-01-01 00:00:00',
            'key_holder'             => '1',
            'trusted'                => '1',
            'induction_completed'    => '1',
            'key_deposit_payment_id' => '1',
            'payment_method'         => 'cash',
            'gift'                   => 'FREEBIE',
            'seen_at'                => '2026-01-01 00:00:00',
            'hash'                   => 'chosen-hash',
        ])->assertRedirect();

        $member = User::where('email', $email)->firstOrFail();
        $this->assertSame(MemberStatus::SettingUp, $member->status);
        $this->assertFalse((bool) $member->active);
        $this->assertFalse((bool) $member->banned);
        $this->assertNull($member->banned_reason);
        $this->assertNull($member->banned_date);
        $this->assertFalse((bool) $member->key_holder);
        $this->assertFalse((bool) $member->trusted);
        $this->assertFalse((bool) $member->induction_completed);
        $this->assertNull($member->key_deposit_payment_id);
        $this->assertNull($member->payment_method);
        $this->assertEmpty($member->gift);
        $this->assertFalse((bool) $member->seen_at?->isSameDay('2026-01-01'));
        $this->assertNotSame('chosen-hash', $member->hash);
        $this->assertSame(30, strlen($member->hash));
    }
}
