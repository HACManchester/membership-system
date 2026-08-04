<?php

namespace BB\Console\Commands;

use BB\Entities\Room;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off data migration: seed the canonical rooms and map each piece of
 * equipment's legacy `room` slug onto the new `room_id` foreign key.
 *
 * Deliberately a command, not a schema migration — schema migrations never
 * migrate data in this codebase. Idempotent, and safe to re-run.
 */
class BackfillEquipmentRooms extends Command
{
    protected $signature = 'equipment:backfill-rooms {--dry-run : Report what would change without writing}';

    protected $description = 'Seed the canonical rooms and backfill equipment.room_id from the legacy room slug';

    /**
     * The real rooms, in display order. Keys are the legacy `equipment.room`
     * slugs; values are the display names.
     */
    private const ROOMS = [
        'stage'        => 'The Stage',
        'woodwork'     => 'Woodwork',
        'welding'      => 'Fabrication',
        'metalworking' => 'Metalwork',
        'main-room'    => 'Main Area',
        'bar'          => 'The Bar',
        'electronics'  => 'Electronics',
        'bikespace'    => 'Bike Space',
    ];

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run — no changes will be written.');
        }

        DB::transaction(function () use ($dryRun) {
            $rooms = $this->seedRooms($dryRun);
            $this->backfillEquipment($rooms, $dryRun);
            $this->warnOnUnmappedRooms($dryRun);
        });

        $this->info('Done.');

        return 0;
    }

    /**
     * @return array<string, int|null> slug => room id (null in dry-run for new rooms)
     */
    private function seedRooms(bool $dryRun): array
    {
        $ids = [];

        foreach (self::ROOMS as $slug => $name) {
            $existing = Room::withTrashed()->where('slug', $slug)->first();

            if ($existing) {
                $ids[$slug] = $existing->id;
                continue;
            }

            $this->line("Create room: {$name} ({$slug})");

            if ($dryRun) {
                $ids[$slug] = null;
                continue;
            }

            $room = Room::create([
                'name' => $name,
                'slug' => $slug,
            ]);
            $ids[$slug] = $room->id;
        }

        return $ids;
    }

    /**
     * @param array<string, int|null> $rooms
     */
    private function backfillEquipment(array $rooms, bool $dryRun): void
    {
        foreach ($rooms as $slug => $id) {
            $query = DB::table('equipment')->where('room', $slug);
            $count = $query->count();

            if ($count === 0) {
                continue;
            }

            $this->line("Map {$count} equipment record(s) in '{$slug}' to room_id.");

            if (! $dryRun && $id !== null) {
                $query->update(['room_id' => $id]);
            }
        }
    }

    private function warnOnUnmappedRooms(bool $dryRun): void
    {
        $unmapped = DB::table('equipment')
            ->whereNotNull('room')
            ->where('room', '!=', '')
            ->whereNotIn('room', array_keys(self::ROOMS))
            ->distinct()
            ->pluck('room');

        foreach ($unmapped as $value) {
            $this->error("Unmapped room value '{$value}' — left with a null room_id. Reassign it manually.");
        }
    }
}
