<?php

namespace BB\Console\Commands;

use BB\Entities\Course;
use BB\Entities\Equipment;
use Illuminate\Console\Command;

class MigrateCourseEquipmentData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courses:migrate-equipment-data 
                            {--dry-run : Show what would be migrated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate equipment induction_category data to course_equipment pivot table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in dry-run mode - no changes will be made');
        }
        
        // Get all equipment with induction_category
        $equipment = Equipment::whereNotNull('induction_category')
            ->where('induction_category', '!=', '')
            ->get();
            
        if ($equipment->isEmpty()) {
            $this->info('No equipment with induction_category found.');
            return 0;
        }
        
        $this->info("Found {$equipment->count()} equipment items with induction_category");
        
        $migrated = 0;
        $skipped = 0;
        $notFound = 0;
        
        foreach ($equipment as $item) {
            // Find the course with matching slug
            $course = Course::where('slug', $item->induction_category)->first();
            
            if (!$course) {
                $this->warn("\nNo course found with slug: {$item->induction_category} for equipment: {$item->name}");
                $notFound++;
                continue;
            }
            
            // Check if relationship already exists
            if ($course->equipment()->where('equipment.id', $item->id)->exists()) {
                $skipped++;
                continue;
            }
            
            if (!$dryRun) {
                // Create the relationship
                $course->equipment()->attach($item->id);
            }
            
            $migrated++;
        }
        
        $this->info("Migration complete!");
        $this->info("Migrated: {$migrated}");
        $this->info("Skipped (already exists): {$skipped}");
        $this->info("Not found (no matching course): {$notFound}");
        
        if ($dryRun) {
            $this->comment('This was a dry run. Run without --dry-run to apply changes.');
        }
        
        return 0;
    }
}