<?php

namespace Tests\Feature;

use BB\Entities\Gift;
use BB\Entities\User;
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
    public function a_new_full_member_can_register()
    {
        $given = $this->faker->firstName();
        $email = $this->faker->unique()->safeEmail();

        $this->post(route('account.store'), [
            'given_name'           => $given,
            'family_name'          => $this->faker->lastName(),
            'email'                => $email,
            'display_name'         => $this->faker->userName(),
            'online_only'          => '0',
            'suppress_real_name'   => '0',
            'password'             => $this->faker->password(10),
            'phone'                => '07700900123',
            'address'              => ['line_1' => '1 Test Street', 'postcode' => 'm4 7hr'],
            'emergency_contact'    => $this->faker->name(),
            'monthly_subscription' => config('membership.prices.minimum'),
            'rules_agreed'         => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email'      => $email,
            'given_name' => $given,
            'status'     => 'setting-up',
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
    public function a_photo_given_at_signup_is_held_for_approval_with_its_privacy_choice()
    {
        Storage::fake('public');
        $email = $this->faker->unique()->safeEmail();

        $this->post(route('account.store'), [
            'given_name'            => $this->faker->firstName(),
            'family_name'           => $this->faker->lastName(),
            'email'                 => $email,
            'display_name'          => $this->faker->userName(),
            'online_only'           => '0',
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
            'online_only'          => '0',
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
            'amount'    => 12.50,
            'status'    => 'paid',
            'reference' => 'GIFT1250',
        ]);
        $this->assertModelMissing($gift);
    }
}
