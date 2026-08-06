<?php

namespace BB\Console\Commands;

use BB\Entities\Equipment;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * One-off data migration: preserve the sparse, rarely-used equipment fields
 * (serial number, colour, asset tag, obtained/removed dates) into the admin-only
 * `admin_notes` field before those fields are dropped from the form and,
 * eventually, the schema. Non-destructive (the source columns are left intact)
 * and idempotent (equipment already carrying the migrated block is skipped).
 */
class MigrateEquipmentLegacyFieldsToNotes extends Command
{
    protected $signature = 'equipment:migrate-legacy-fields-to-notes {--dry-run : Report what would change without writing}';

    protected $description = 'Copy legacy equipment fields into admin_notes in a consistent, labelled format';

    private const MARKER = '--- Migrated from legacy fields';

    /**
     * Legacy attribute => label, in the order they appear in the note block.
     */
    private const FIELDS = [
        'serial_number' => 'Serial number',
        'colour'        => 'Colour',
        'asset_tag_id'  => 'Asset tag',
        'obtained_at'   => 'Obtained',
        'removed_at'    => 'Removed',
    ];

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run — no changes will be written.');
        }

        $migrated = 0;

        foreach (Equipment::withTrashed()->get() as $equipment) {
            if (Str::contains((string) $equipment->admin_notes, self::MARKER)) {
                continue;
            }

            $lines = $this->legacyLines($equipment);
            if (empty($lines)) {
                continue;
            }

            $block = self::MARKER . ' (' . now()->toDateString() . ') ---' . PHP_EOL
                . implode(PHP_EOL, $lines);

            $this->line("Migrate legacy fields for: {$equipment->name}");

            if (! $dryRun) {
                $equipment->admin_notes = trim(
                    ($equipment->admin_notes ? $equipment->admin_notes . PHP_EOL . PHP_EOL : '') . $block
                );
                $equipment->save();
            }

            $migrated++;
        }

        $this->info("Done. {$migrated} record(s) " . ($dryRun ? 'would be' : '') . ' migrated.');

        return 0;
    }

    /**
     * @return array<int, string>
     */
    private function legacyLines(Equipment $equipment): array
    {
        $lines = [];

        foreach (self::FIELDS as $attribute => $label) {
            $value = $equipment->getAttribute($attribute);

            if ($value === null || $value === '') {
                continue;
            }

            if (in_array($attribute, ['obtained_at', 'removed_at'], true)) {
                $value = $value->toDateString();
            }

            $lines[] = "{$label}: {$value}";
        }

        return $lines;
    }
}
