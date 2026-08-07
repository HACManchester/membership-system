<?php

namespace Tests\Feature;

use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_for_an_admin()
    {
        $admin = factory(User::class)->state('admin')->create();

        $this->actingAs($admin)->get(route('admin'))
            ->assertStatus(200)
            ->assertInertia(function ($page) {
                $page->component('Admin/Index')->has('urls');
            });
    }

    public function test_admin_dashboard_is_forbidden_for_a_regular_member()
    {
        $member = factory(User::class)->create();

        $this->actingAs($member)->get(route('admin'))->assertStatus(403);
    }

    public function test_home_renders_for_a_guest()
    {
        $this->get(route('home'))
            ->assertStatus(200)
            ->assertInertia(function ($page) {
                $page->component('Home')->has('urls');
            });
    }

    public function test_home_redirects_a_logged_in_member_to_their_account()
    {
        $member = factory(User::class)->create();

        $this->actingAs($member)->get(route('home'))
            ->assertRedirect(route('account.show', $member->id));
    }
}
