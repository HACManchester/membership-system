<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SignupTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function the_signup_page_is_reachable()
    {
        $this->get('/register')->assertStatus(200)->assertSee('Join');
    }

    /** @test */
    public function a_new_full_member_can_register()
    {
        $given = $this->faker->firstName;
        $email = $this->faker->unique()->safeEmail;

        $this->post(route('account.store'), [
            'given_name'           => $given,
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

        $this->assertDatabaseHas('users', [
            'email'      => $email,
            'given_name' => $given,
            'status'     => 'setting-up',
        ]);
    }
}
