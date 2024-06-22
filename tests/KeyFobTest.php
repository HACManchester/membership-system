<?php

use BB\Entities\KeyFob;
use BB\Entities\Role;
use BB\Entities\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\BrowserKitTestCase;

class KeyFobTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    // Can register keyfob against self
    // Cannot register keyfob against others
    // Admins can register keyfob against others

    // Can register access code against self
    // Cannot register access code against others
    // Admins can register access code against others

    // Can mark own access code as lost
    // Cannot mark other people's access code as lost
    // Admins can mark other people's access code as lost

    public function test_must_complete_general_induction_before_accessing_keyfobs()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user);

        $this->get("/account/{$user->id}/keyfobs")
            ->seeStatusCode(200)
            ->see("You need to have been given the general induction before you can add access methods.");
    }

    public function test_can_view_own_keyfobs()
    {
        $user = factory(User::class)->create([
            'induction_completed' => true,
        ]);
        $keyfobs = factory(KeyFob::class)->times(3)->create([
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $this->get("/account/{$user->id}/keyfobs")
            ->seeStatusCode(200)
            ->see($keyfobs[0]->key_id)
            ->see($keyfobs[1]->key_id)
            ->see($keyfobs[2]->key_id);
    }

    public function test_cannot_view_other_peoples_keyfobs()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $this->actingAs($user);

        $this->get("/account/{$otherUser->id}/keyfobs")
            ->seeStatusCode(403);
    }

    public function test_admins_can_view_other_peoples_keyfobs()
    {
        $adminUser = factory(User::class)->create();
        $adminUser->assignRole(Role::findByName('admin'));

        $otherUser = factory(User::class)->create();
        $keyfobs = factory(KeyFob::class)->times(3)->create([
            'user_id' => $otherUser->id
        ]);

        $this->actingAs($adminUser);

        $this->get("/account/{$otherUser->id}/keyfobs")
            ->seeStatusCode(200)
            ->see($keyfobs[0]->key_id)
            ->see($keyfobs[1]->key_id)
            ->see($keyfobs[2]->key_id);
    }

    public function test_can_add_keyfob_to_self()
    {
        $user = factory(User::class)->create([
            'induction_completed' => true,
        ]);

        $this->actingAs($user);

        $this->post("/account/{$user->id}/keyfobs", [
            'type' => 'keyfob',
            'key_id' => sprintf('%08X', mt_rand(0, 0xFFFFFFFF)),
        ])
            ->assertRedirectedTo("/account/{$user->id}/keyfobs")
            ->followRedirects()
            ->see('Key fob/Access code has been activated');
    }

    public function test_cannot_add_keyfob_to_others()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create(['induction_completed' => true]);

        $this->actingAs($user);

        $this->post("/account/{$otherUser->id}/keyfobs", [
            'key_id' => sprintf('%08X', mt_rand(0, 0xFFFFFFFF)),
        ])
            ->seeStatusCode(403);
    }

    public function test_admins_can_add_keyfob_to_others()
    {
        $adminUser = factory(User::class)->create();
        $adminUser->assignRole(Role::findByName('admin'));

        $otherUser = factory(User::class)->create(['induction_completed' => true]);

        $this->actingAs($adminUser);

        $this->post("/account/{$otherUser->id}/keyfobs", [
            'type' => 'keyfob',
            'key_id' => sprintf('%08X', mt_rand(0, 0xFFFFFFFF)),
        ])
            ->assertRedirectedTo("/account/{$otherUser->id}/keyfobs")
            ->followRedirects()
            ->see('Key fob/Access code has been activated');
    }

    public function test_can_mark_own_keyfobs_as_lost()
    {
        $user = factory(User::class)->create([
            'induction_completed' => true,
        ]);
        $keyfob = factory(KeyFob::class)->create([
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $this->delete("/account/{$user->id}/keyfobs/{$keyfob->id}")
            ->assertRedirectedTo("/account/{$user->id}/keyfobs")
            ->followRedirects()
            ->see('Key Fob marked as lost/broken');
    }

    public function test_cannot_mark_others_keyfobs_as_lost()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create(['induction_completed' => true]);
        $keyfob = factory(KeyFob::class)->create([
            'user_id' => $otherUser->id
        ]);

        $this->actingAs($user);

        $this->delete("/account/{$otherUser->id}/keyfobs/{$keyfob->id}")
            ->seeStatusCode(403);
    }

    public function test_admins_can_mark_others_keyfobs_as_lost()
    {
        $adminUser = factory(User::class)->create();
        $adminUser->assignRole(Role::findByName('admin'));

        $otherUser = factory(User::class)->create(['induction_completed' => true]);
        $keyfob = factory(KeyFob::class)->create([
            'user_id' => $otherUser->id
        ]);

        $this->actingAs($adminUser);

        $this->delete("/account/{$otherUser->id}/keyfobs/{$keyfob->id}")
            ->assertRedirectedTo("/account/{$otherUser->id}/keyfobs")
            ->followRedirects()
            ->see('Key Fob marked as lost/broken');
    }
}
