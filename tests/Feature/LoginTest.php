<?php

namespace Tests\Feature;

use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_member_can_log_in_with_valid_credentials()
    {
        $password = Str::random(10);
        $user = factory(User::class)->create(['password' => $password]);

        $this->post(route('session.store'), [
            'email'    => $user->email,
            'password' => $password,
        ])->assertRedirect("account/{$user->id}");

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function invalid_credentials_are_rejected()
    {
        $this->from(route('login'))->post(route('session.store'), [
            'email'    => 'unknown@example.com',
            'password' => 'wrong-password',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
