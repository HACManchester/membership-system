<?php

namespace Tests\Feature;

use BB\Entities\KeyFob;
use BB\Entities\Role;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeyFobTest extends TestCase
{
    use RefreshDatabase;

    private function admin()
    {
        $user = factory(User::class)->create();
        $user->assignRole(Role::findByName('admin'));

        return $user;
    }

    /** @test */
    public function general_induction_is_required_before_adding_access_methods()
    {
        $user = factory(User::class)->create(['induction_completed' => false]);

        $this->actingAs($user)
            ->get("/account/{$user->id}/keyfobs")
            ->assertStatus(200)
            ->assertSee('You need to have been given the general induction before you can add access methods.');
    }

    /** @test */
    public function a_member_can_view_their_own_keyfobs()
    {
        $user = factory(User::class)->create(['induction_completed' => true]);
        $fobs = factory(KeyFob::class)->times(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/account/{$user->id}/keyfobs")->assertStatus(200);
        foreach ($fobs as $fob) {
            $response->assertSee($fob->key_id);
        }
    }

    /** @test */
    public function a_member_cannot_view_another_members_keyfobs()
    {
        $user = factory(User::class)->create();
        $other = factory(User::class)->create();

        $this->actingAs($user)->get("/account/{$other->id}/keyfobs")->assertStatus(403);
    }

    /** @test */
    public function an_admin_can_view_another_members_keyfobs()
    {
        $other = factory(User::class)->create();
        $fobs = factory(KeyFob::class)->times(3)->create(['user_id' => $other->id]);

        $response = $this->actingAs($this->admin())->get("/account/{$other->id}/keyfobs")->assertStatus(200);
        foreach ($fobs as $fob) {
            $response->assertSee($fob->key_id);
        }
    }

    /** @test */
    public function a_member_can_add_a_keyfob_to_themselves()
    {
        $user = factory(User::class)->create(['induction_completed' => true]);
        $keyId = sprintf('%08X', 0xABCDEF12);

        $this->actingAs($user)
            ->post("/account/{$user->id}/keyfobs", ['type' => 'keyfob', 'key_id' => $keyId])
            ->assertRedirect("/account/{$user->id}/keyfobs");

        $this->assertDatabaseHas('key_fobs', ['user_id' => $user->id, 'key_id' => $keyId, 'active' => true]);
    }

    /** @test */
    public function a_member_cannot_add_a_keyfob_to_another_member()
    {
        $user = factory(User::class)->create();
        $other = factory(User::class)->create(['induction_completed' => true]);

        $this->actingAs($user)
            ->post("/account/{$other->id}/keyfobs", ['type' => 'keyfob', 'key_id' => sprintf('%08X', 0x11112222)])
            ->assertStatus(403);

        $this->assertEquals(0, KeyFob::where('user_id', $other->id)->count());
    }

    /** @test */
    public function an_admin_can_add_a_keyfob_to_another_member()
    {
        $other = factory(User::class)->create(['induction_completed' => true]);
        $keyId = sprintf('%08X', 0x33334444);

        $this->actingAs($this->admin())
            ->post("/account/{$other->id}/keyfobs", ['type' => 'keyfob', 'key_id' => $keyId])
            ->assertRedirect("/account/{$other->id}/keyfobs");

        $this->assertDatabaseHas('key_fobs', ['user_id' => $other->id, 'key_id' => $keyId]);
    }

    /** @test */
    public function a_member_can_mark_their_own_keyfob_as_lost()
    {
        $user = factory(User::class)->create(['induction_completed' => true]);
        $fob = factory(KeyFob::class)->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->delete("/account/{$user->id}/keyfobs/{$fob->id}")
            ->assertRedirect("/account/{$user->id}/keyfobs");

        $this->assertDatabaseHas('key_fobs', ['id' => $fob->id, 'lost' => true, 'active' => false]);
    }

    /** @test */
    public function a_member_cannot_mark_another_members_keyfob_as_lost()
    {
        $user = factory(User::class)->create();
        $other = factory(User::class)->create(['induction_completed' => true]);
        $fob = factory(KeyFob::class)->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->delete("/account/{$other->id}/keyfobs/{$fob->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('key_fobs', ['id' => $fob->id, 'active' => true]);
    }

    /** @test */
    public function an_admin_can_mark_another_members_keyfob_as_lost()
    {
        $other = factory(User::class)->create(['induction_completed' => true]);
        $fob = factory(KeyFob::class)->create(['user_id' => $other->id]);

        $this->actingAs($this->admin())
            ->delete("/account/{$other->id}/keyfobs/{$fob->id}")
            ->assertRedirect("/account/{$other->id}/keyfobs");

        $this->assertDatabaseHas('key_fobs', ['id' => $fob->id, 'lost' => true, 'active' => false]);
    }
}
