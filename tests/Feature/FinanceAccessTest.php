<?php

namespace Tests\Feature;

use BB\Entities\Role;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_guest_is_redirected_to_login_from_the_payments_page()
    {
        $this->get('/payments')->assertRedirect(route('login'));
    }

    /** @test */
    public function a_regular_member_cannot_view_the_payments_page()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)->get('/payments')->assertStatus(403);
    }

    /** @test */
    public function a_finance_role_member_can_view_the_payments_page()
    {
        $user = factory(User::class)->create();
        $user->assignRole(Role::findByName('finance'));

        $this->actingAs($user)->get('/payments')->assertStatus(200);
    }
}
