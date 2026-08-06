<?php

namespace Tests\Feature;

use BB\Entities\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MigrateEquipmentLegacyFieldsToNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_copies_legacy_fields_into_admin_notes()
    {
        $equipment = factory(Equipment::class)->create([
            'serial_number' => 'SN123',
            'colour' => 'Blue',
            'asset_tag_id' => 'AT-1',
            'obtained_at' => '2020-01-01',
        ]);

        Artisan::call('equipment:migrate-legacy-fields-to-notes');

        $notes = $equipment->fresh()->admin_notes;
        $this->assertStringContainsString('--- Migrated from legacy fields', $notes);
        $this->assertStringContainsString('Serial number: SN123', $notes);
        $this->assertStringContainsString('Colour: Blue', $notes);
        $this->assertStringContainsString('Asset tag: AT-1', $notes);
        $this->assertStringContainsString('Obtained: 2020-01-01', $notes);
    }

    public function test_it_skips_equipment_with_no_legacy_data()
    {
        $equipment = factory(Equipment::class)->create(['obtained_at' => null]);

        Artisan::call('equipment:migrate-legacy-fields-to-notes');

        $this->assertNull($equipment->fresh()->admin_notes);
    }

    public function test_it_is_idempotent()
    {
        $equipment = factory(Equipment::class)->create([
            'serial_number' => 'SN123',
            'obtained_at' => null,
        ]);

        Artisan::call('equipment:migrate-legacy-fields-to-notes');
        Artisan::call('equipment:migrate-legacy-fields-to-notes');

        $this->assertEquals(
            1,
            substr_count($equipment->fresh()->admin_notes, '--- Migrated from legacy fields')
        );
    }

    public function test_dry_run_writes_nothing()
    {
        $equipment = factory(Equipment::class)->create([
            'serial_number' => 'SN123',
            'obtained_at' => null,
        ]);

        Artisan::call('equipment:migrate-legacy-fields-to-notes', ['--dry-run' => true]);

        $this->assertNull($equipment->fresh()->admin_notes);
    }
}
