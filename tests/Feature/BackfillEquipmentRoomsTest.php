<?php

namespace Tests\Feature;

use BB\Entities\Equipment;
use BB\Entities\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackfillEquipmentRoomsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The legacy `room` column is no longer mass-assignable, so set it directly
     * to simulate existing production data the backfill runs against.
     */
    private function equipmentInRoom(?string $room): Equipment
    {
        $equipment = factory(Equipment::class)->create();
        $equipment->room = $room;
        $equipment->save();

        return $equipment;
    }

    public function test_it_seeds_rooms_and_maps_equipment()
    {
        $woodwork = $this->equipmentInRoom('woodwork');
        $welding = $this->equipmentInRoom('welding');

        Artisan::call('equipment:backfill-rooms');

        $this->assertEquals(8, Room::count());

        $woodworkRoom = Room::where('slug', 'woodwork')->first();
        $weldingRoom = Room::where('slug', 'welding')->first();

        $this->assertEquals($woodworkRoom->id, $woodwork->fresh()->room_id);
        $this->assertEquals($weldingRoom->id, $welding->fresh()->room_id);
    }

    public function test_unmapped_room_values_are_left_null_and_not_created()
    {
        $stray = $this->equipmentInRoom('visual-arts');

        Artisan::call('equipment:backfill-rooms');

        $this->assertNull(Room::where('slug', 'visual-arts')->first());
        $this->assertNull($stray->fresh()->room_id);
    }

    public function test_null_room_is_left_null()
    {
        $noRoom = $this->equipmentInRoom(null);

        Artisan::call('equipment:backfill-rooms');

        $this->assertNull($noRoom->fresh()->room_id);
    }

    public function test_dry_run_writes_nothing()
    {
        $equipment = $this->equipmentInRoom('woodwork');

        Artisan::call('equipment:backfill-rooms', ['--dry-run' => true]);

        $this->assertEquals(0, Room::count());
        $this->assertNull($equipment->fresh()->room_id);
    }

    public function test_it_is_idempotent()
    {
        $this->equipmentInRoom('woodwork');

        Artisan::call('equipment:backfill-rooms');
        Artisan::call('equipment:backfill-rooms');

        $this->assertEquals(8, Room::count());
    }
}
