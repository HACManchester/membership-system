<?php

namespace Tests\Feature;

use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Some older accounts have no profile_data row. Admin pages that list or show
 * them must still render.
 */
class MemberWithoutProfileTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_admin_member_list_shows_a_member_without_a_profile()
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('account.index'))
            ->assertOk()
            ->assertSee($member->email);
    }

    #[Test]
    public function an_admin_can_view_the_account_of_a_member_without_a_profile()
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('account.show', $member->id))
            ->assertOk()
            ->assertDontSee('New photo to approve');
    }
}
