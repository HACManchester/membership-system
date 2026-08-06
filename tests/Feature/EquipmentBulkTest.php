<?php

namespace Tests\Feature;

use BB\Entities\Course;
use BB\Entities\Equipment;
use BB\Entities\Room;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentBulkTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return factory(User::class)->state('admin')->create();
    }

    public function test_admin_can_bulk_create_equipment()
    {
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);

        $response = $this->actingAs($this->admin())->post(route('equipment.bulk-store'), [
            'room_id' => $room->id,
            'items' => [
                ['name' => 'Bambu P1S #1'],
                ['name' => 'Bambu P1S #2'],
            ],
        ]);

        $response->assertRedirect(route('equipment.index'));
        $this->assertDatabaseHas('equipment', ['slug' => 'bambu-p1s-1']);
        $this->assertDatabaseHas('equipment', ['slug' => 'bambu-p1s-2']);
    }

    public function test_bulk_create_links_a_course_and_flags_induction()
    {
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);
        $course = factory(Course::class)->create();

        $this->actingAs($this->admin())->post(route('equipment.bulk-store'), [
            'room_id' => $room->id,
            'course_id' => $course->id,
            'items' => [['name' => 'Course Tool']],
        ]);

        $equipment = Equipment::where('slug', 'course-tool')->first();
        $this->assertTrue((bool) $equipment->requires_induction);
        $this->assertEquals(1, $equipment->courses()->count());
    }

    public function test_duplicate_slug_within_the_batch_is_rejected()
    {
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);

        $this->actingAs($this->admin())->post(route('equipment.bulk-store'), [
            'room_id' => $room->id,
            'items' => [
                ['name' => 'Same Name'],
                ['name' => 'Same Name'],
            ],
        ])->assertSessionHasErrors('items.1.slug');

        $this->assertEquals(0, Equipment::where('slug', 'same-name')->count());
    }

    public function test_duplicate_slug_against_existing_equipment_is_rejected()
    {
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);
        factory(Equipment::class)->create(['slug' => 'existing-tool']);

        $this->actingAs($this->admin())->post(route('equipment.bulk-store'), [
            'room_id' => $room->id,
            'items' => [['name' => 'Existing Tool']],
        ])->assertSessionHasErrors('items.0.slug');
    }

    public function test_non_authorised_user_cannot_bulk_create()
    {
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);
        $user = factory(User::class)->create();

        $this->actingAs($user)->post(route('equipment.bulk-store'), [
            'room_id' => $room->id,
            'items' => [['name' => 'Nope']],
        ])->assertForbidden();
    }

    public function test_bulk_create_page_renders()
    {
        $this->actingAs($this->admin())->get(route('equipment.bulk-create'))
            ->assertInertia(function ($page) {
                $page->component('Equipment/BulkCreate')->has('rooms');
            });
    }
}
