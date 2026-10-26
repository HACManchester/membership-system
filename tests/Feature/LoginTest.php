<?php

namespace Tests\Feature;

use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_member_can_log_in_with_valid_credentials()
    {
        $password = Str::random(10);
        $user = User::factory()->create(['password' => $password]);

        $this->post(route('session.store'), [
            'email'    => $user->email,
            'password' => $password,
        ])->assertRedirect("account/{$user->id}");

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function invalid_credentials_are_rejected()
    {
        $this->from(route('login'))->post(route('session.store'), [
            'email'    => 'unknown@example.com',
            'password' => 'wrong-password',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
    }

    #[Test]
    public function a_password_stored_without_bcrypt_is_a_failed_login_not_an_error()
    {
        $user = User::factory()->create();
        DB::table('users')->where('id', $user->id)->update(['password' => md5('old-password')]);

        $this->from(route('login'))->post(route('session.store'), [
            'email'    => $user->email,
            'password' => 'old-password',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
    }

    #[Test]
    public function logging_in_rehashes_a_password_saved_with_an_older_cost()
    {
        $password = Str::random(10);
        $user = User::factory()->create();
        $oldHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 5]);
        DB::table('users')->where('id', $user->id)->update(['password' => $oldHash]);

        $this->post(route('session.store'), [
            'email'    => $user->email,
            'password' => $password,
        ])->assertRedirect("account/{$user->id}");

        $newHash = DB::table('users')->where('id', $user->id)->value('password');
        $this->assertNotSame($oldHash, $newHash);
        $this->assertSame((int) config('hashing.bcrypt.rounds'), password_get_info($newHash)['options']['cost']);
    }
}
