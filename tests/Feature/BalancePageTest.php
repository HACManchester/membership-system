<?php

namespace Tests\Feature;

use BB\Entities\User;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BalancePageTest extends TestCase
{
    #[Test]
    public function the_balance_is_shown_in_pounds_from_the_pence_stored()
    {
        $member = User::factory()->create(['cash_balance' => 1234]);

        $this->actingAs($member)->get(route('account.balance.index', $member->id))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Balance/Index')
                ->where('balance', ['formatted' => '£12.34', 'negative' => false]));
    }

    #[Test]
    public function a_negative_balance_puts_the_sign_before_the_pound()
    {
        $member = User::factory()->create(['cash_balance' => -250]);

        $this->actingAs($member)->get(route('account.balance.index', $member->id))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('balance', ['formatted' => '-£2.50', 'negative' => true]));
    }

    #[Test]
    public function a_balance_under_a_pound_overdrawn_is_still_negative()
    {
        $member = User::factory()->create(['cash_balance' => -50]);

        $this->actingAs($member)->get(route('account.balance.index', $member->id))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('balance', ['formatted' => '-£0.50', 'negative' => true]));
    }

    #[Test]
    public function the_page_sends_no_member_details()
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('account.balance.index', $member->id))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Balance/Index')
                ->missing('user')
                ->missing('memberList'));
    }

    #[Test]
    public function a_guest_is_sent_to_log_in()
    {
        $member = User::factory()->create();

        $this->get(route('account.balance.index', $member->id))->assertRedirect(route('login'));
    }
}
