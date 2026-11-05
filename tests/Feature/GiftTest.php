<?php

namespace Tests\Feature;

use BB\Entities\User;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GiftTest extends TestCase
{
    #[Test]
    public function a_guest_gets_the_gift_code_form_which_submits_to_registration()
    {
        $this->get(route('gift'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Gift')
                ->where('urls', ['register' => '/register']));
    }

    #[Test]
    public function a_member_can_open_the_gift_page()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('gift'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Gift'));
    }
}
