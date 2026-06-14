<?php

namespace Tests\Feature;

use BB\Entities\Role;
use BB\Entities\StorageBox;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Note on the current authorization model (StorageBoxPolicy): claiming a box is restricted to
 * admins and the `storage` role (via the policy `before()` hook) — `claim()` itself returns false,
 * so regular members cannot self-claim. Releasing is allowed for the box's owner or an admin.
 * These tests pin that behaviour; whether self-service claiming should be enabled is a product
 * question, not assumed here.
 */
class StorageBoxClaimTest extends TestCase
{
    use RefreshDatabase;

    private function storageTeamMember()
    {
        $user = factory(User::class)->create();
        $user->assignRole(Role::findByName('storage'));

        return $user;
    }

    /** @test */
    public function a_storage_team_member_can_claim_an_unclaimed_box()
    {
        $staff = $this->storageTeamMember();
        $box = factory(StorageBox::class)->create(['user_id' => 0]);

        $this->actingAs($staff)
            ->post(route('storage_boxes_claim.update', $box->id))
            ->assertRedirect();

        $this->assertEquals($staff->id, $box->fresh()->user_id);
    }

    /** @test */
    public function a_regular_member_cannot_claim_a_box()
    {
        $member = factory(User::class)->create();
        $box = factory(StorageBox::class)->create(['user_id' => 0]);

        $this->actingAs($member)
            ->post(route('storage_boxes_claim.update', $box->id))
            ->assertStatus(403);

        $this->assertEquals(0, $box->fresh()->user_id);
    }

    /** @test */
    public function an_already_claimed_box_cannot_be_claimed_again()
    {
        $owner = factory(User::class)->create();
        $box = factory(StorageBox::class)->create(['user_id' => $owner->id]);
        $staff = $this->storageTeamMember();

        $this->actingAs($staff)
            ->post(route('storage_boxes_claim.update', $box->id))
            ->assertRedirect();

        // Unchanged — still owned by the original claimant
        $this->assertEquals($owner->id, $box->fresh()->user_id);
    }

    /** @test */
    public function a_member_can_release_their_own_box()
    {
        $owner = factory(User::class)->create();
        $box = factory(StorageBox::class)->create(['user_id' => $owner->id]);

        $this->actingAs($owner)
            ->delete(route('storage_boxes_claim.destroy', $box->id))
            ->assertRedirect();

        $this->assertEquals(0, $box->fresh()->user_id);
    }

    /** @test */
    public function a_member_cannot_release_someone_elses_box()
    {
        $owner = factory(User::class)->create();
        $box = factory(StorageBox::class)->create(['user_id' => $owner->id]);
        $other = factory(User::class)->create();

        $this->actingAs($other)
            ->delete(route('storage_boxes_claim.destroy', $box->id))
            ->assertStatus(403);

        $this->assertEquals($owner->id, $box->fresh()->user_id);
    }
}
