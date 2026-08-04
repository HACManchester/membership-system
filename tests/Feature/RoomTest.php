<?php

namespace Tests\Feature;

use BB\Entities\Equipment;
use BB\Entities\Room;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_view_rooms_index()
    {
        $user = factory(User::class)->state('admin')->create();
        factory(Room::class)->create();

        $response = $this->actingAs($user)->get(route('room.index'));

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('Rooms/Index')->has('rooms', 1);
        });
    }

    public function test_admin_can_create_room()
    {
        $admin = factory(User::class)->state('admin')->create();

        $response = $this->actingAs($admin)->post(route('room.store'), [
            'name' => 'Test Room',
            'slug' => 'test-room',
            'description' => 'A place for things',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('rooms', [
            'name' => 'Test Room',
            'slug' => 'test-room',
        ]);
    }

    public function test_slug_is_generated_from_name_when_omitted()
    {
        $admin = factory(User::class)->state('admin')->create();

        $this->actingAs($admin)->post(route('room.store'), [
            'name' => 'The Big Workshop',
        ]);

        $this->assertDatabaseHas('rooms', ['slug' => 'the-big-workshop']);
    }

    public function test_duplicate_name_is_rejected()
    {
        $admin = factory(User::class)->state('admin')->create();
        factory(Room::class)->create(['name' => 'Woodwork', 'slug' => 'woodwork']);

        $response = $this->actingAs($admin)->post(route('room.store'), [
            'name' => 'Woodwork',
            'slug' => 'woodwork-2',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_room()
    {
        $admin = factory(User::class)->state('admin')->create();
        $room = factory(Room::class)->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $response = $this->actingAs($admin)->put(route('room.update', $room), [
            'name' => 'New Name',
            'slug' => 'old-name',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'name' => 'New Name']);
    }

    public function test_admin_can_delete_empty_room()
    {
        $admin = factory(User::class)->state('admin')->create();
        $room = factory(Room::class)->create();

        $response = $this->actingAs($admin)->delete(route('room.destroy', $room));

        $response->assertRedirect(route('room.index'));
        $this->assertSoftDeleted('rooms', ['id' => $room->id]);
    }

    public function test_room_with_equipment_cannot_be_deleted()
    {
        $admin = factory(User::class)->state('admin')->create();
        $room = factory(Room::class)->create();
        factory(Equipment::class)->create(['room_id' => $room->id]);

        $response = $this->actingAs($admin)->delete(route('room.destroy', $room));

        $response->assertRedirect(route('room.show', $room));
        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'deleted_at' => null]);
    }

    public function test_non_admin_cannot_create_room()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post(route('room.store'), [
            'name' => 'Nope',
            'slug' => 'nope',
        ]);

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_update_room()
    {
        $user = factory(User::class)->create();
        $room = factory(Room::class)->create();

        $response = $this->actingAs($user)->put(route('room.update', $room), [
            'name' => 'Hacked',
            'slug' => $room->slug,
        ]);

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_delete_room()
    {
        $user = factory(User::class)->create();
        $room = factory(Room::class)->create();

        $response = $this->actingAs($user)->delete(route('room.destroy', $room));

        $response->assertForbidden();
    }
}
