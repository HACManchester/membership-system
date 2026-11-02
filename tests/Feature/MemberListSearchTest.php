<?php

namespace Tests\Feature;

use BB\Entities\ProfileData;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The admin member list's "show left" and "online only" toggles must narrow
 * search results too, not just the unfiltered list.
 */
class MemberListSearchTest extends TestCase
{
    use RefreshDatabase;

    private function member(array $attributes): User
    {
        $user = User::factory()->create($attributes);
        ProfileData::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    private function searchAsAdmin(array $query): array
    {
        $admin = User::factory()->admin()->create(['family_name' => 'Admin']);

        $response = $this->actingAs($admin)
            ->get(route('account.index', ['filter' => 'Lovelace', ...$query]))
            ->assertOk();

        return $response->viewData('users')->pluck('id')->sort()->values()->all();
    }

    #[Test]
    public function a_search_leaves_out_members_who_have_left_by_default()
    {
        $active = $this->member(['family_name' => 'Lovelace']);
        $this->member(['family_name' => 'Lovelace', 'status' => 'left', 'active' => false]);

        $this->assertSame([$active->id], $this->searchAsAdmin([]));
    }

    #[Test]
    public function a_search_with_show_left_finds_only_members_who_have_left()
    {
        $this->member(['family_name' => 'Lovelace']);
        $left = $this->member(['family_name' => 'Lovelace', 'status' => 'left', 'active' => false]);

        $this->assertSame([$left->id], $this->searchAsAdmin(['showLeft' => 1]));
    }

    #[Test]
    public function a_search_leaves_out_online_only_members_unless_they_are_included()
    {
        $member = $this->member(['family_name' => 'Lovelace', 'online_only' => 0]);
        $onlineOnly = $this->member(['family_name' => 'Lovelace', 'online_only' => 1]);

        $this->assertSame([$member->id], $this->searchAsAdmin([]));
        $this->assertSame([$member->id, $onlineOnly->id], $this->searchAsAdmin(['include_online_only' => 1]));
    }
}
