<?php

namespace Tests\Feature;

use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_matching_active_members_only()
    {
        $viewer = factory(User::class)->state('admin')->create();

        $ada = factory(User::class)->create([
            'display_name' => 'Ada',
            'given_name' => 'Ada',
            'family_name' => 'Lovelace',
            'active' => true,
        ]);
        $bob = factory(User::class)->create([
            'display_name' => 'Bob',
            'given_name' => 'Bob',
            'family_name' => 'Smith',
            'active' => true,
        ]);
        $inactive = factory(User::class)->create([
            'display_name' => 'AdaB',
            'given_name' => 'Ada',
            'family_name' => 'Byron',
            'active' => false,
        ]);

        $response = $this->actingAs($viewer)->getJson(route('members.search', ['q' => 'Ada']));
        $response->assertStatus(200);

        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($ada->id));
        $this->assertFalse($ids->contains($bob->id), 'Non-matching member should be excluded');
        $this->assertFalse($ids->contains($inactive->id), 'Inactive member should be excluded');
    }

    public function test_it_requires_a_logged_in_member()
    {
        $response = $this->getJson(route('members.search', ['q' => 'Ada']));
        $this->assertNotEquals(200, $response->status());
    }

    public function test_it_is_forbidden_for_members_who_dont_manage_inductions()
    {
        $regular = factory(User::class)->create();

        $this->actingAs($regular)
            ->getJson(route('members.search', ['q' => 'Ada']))
            ->assertForbidden();
    }

    public function test_a_trainer_may_search()
    {
        $trainer = factory(User::class)->create();
        (new \BB\Entities\TrainingRecord([
            'user_id' => $trainer->id,
            'key' => 'some-tool',
            'is_trainer' => true,
        ]))->save();

        $this->actingAs($trainer)
            ->getJson(route('members.search', ['q' => 'Ada']))
            ->assertStatus(200);
    }
}
