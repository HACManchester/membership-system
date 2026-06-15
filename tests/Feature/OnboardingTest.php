<?php

namespace Tests\Feature;

use BB\Entities\Settings;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * End-to-end happy path for a new full member:
 *   register → confirm email → complete the general induction → register a key fob.
 * Each step asserts the state change it is responsible for.
 */
class OnboardingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    const INDUCTION_CODE = 'OPEN-SESAME';

    /** @test */
    public function a_new_member_can_progress_through_the_full_onboarding_journey()
    {
        Settings::change('general_induction_code', self::INDUCTION_CODE);

        $email = $this->faker->unique()->safeEmail;

        // 1. Register
        $this->post(route('account.store'), [
            'given_name'           => 'Ada',
            'family_name'          => $this->faker->lastName,
            'email'                => $email,
            'display_name'         => $this->faker->userName,
            'online_only'          => '0',
            'suppress_real_name'   => '0',
            'password'             => $this->faker->password(10),
            'phone'                => '07700900123',
            'address'              => ['line_1' => $this->faker->streetAddress, 'postcode' => 'M4 7HR'],
            'emergency_contact'    => $this->faker->name,
            'monthly_subscription' => config('membership.prices.minimum'),
            'rules_agreed'         => '1',
        ])->assertRedirect();

        $user = User::where('email', $email)->firstOrFail();
        $this->assertEquals('setting-up', $user->status);
        $this->assertFalse((bool) $user->email_verified);

        // 2. Confirm email (the link emailed to them — unauthenticated, keyed on the user hash)
        $this->get(route('account.confirm-email', ['id' => $user->id, 'hash' => $user->hash]))
            ->assertRedirect(route('account.show', $user->id));
        $this->assertTrue((bool) $user->fresh()->email_verified);

        // 3. Complete the general induction
        $this->actingAs($user->fresh())
            ->put(route('general-induction.update'), ['induction_code' => self::INDUCTION_CODE])
            ->assertRedirect(route('account.show', $user->id));
        $this->assertTrue((bool) $user->fresh()->induction_completed);

        // 4. Register a key fob (only possible once inducted)
        $keyId = sprintf('%08X', 0xA1B2C3D4);
        $this->actingAs($user->fresh())
            ->post("/account/{$user->id}/keyfobs", ['type' => 'keyfob', 'key_id' => $keyId])
            ->assertRedirect("/account/{$user->id}/keyfobs");

        $this->assertDatabaseHas('key_fobs', ['user_id' => $user->id, 'key_id' => $keyId, 'active' => true]);
    }

    /** @test */
    public function a_key_fob_cannot_be_registered_before_the_general_induction()
    {
        $user = factory(User::class)->create([
            'online_only' => false,
            'induction_completed' => false,
        ]);

        $this->actingAs($user)
            ->post("/account/{$user->id}/keyfobs", ['type' => 'keyfob', 'key_id' => sprintf('%08X', 0x5566)])
            ->assertRedirect("/account/{$user->id}/keyfobs");

        $this->assertEquals(0, $user->keyFobs()->count());
    }
}
