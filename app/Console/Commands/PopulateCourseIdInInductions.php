<?php

namespace BB\Console\Commands;

use Illuminate\Console\Command;
use BB\Entities\Induction;
use BB\Entities\Course;
use BB\Entities\Equipment;

class PopulateCourseIdInInductions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inductions:populate-course-id {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate course_id in inductions table by matching keys to course slugs';
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('Running in DRY RUN mode - no changes will be made');
        }
        
        // Get all unique induction keys
        $inductionKeys = Induction::distinct()->pluck('key')->filter();
        $this->info("Found {$inductionKeys->count()} unique induction keys");
        
        // Get all courses
        $courses = Course::all()->keyBy('slug');
        $this->info("Found {$courses->count()} courses");
        
        // Track results
        $matched = 0;
        $unmatched = [];
        
        // First, map keys to equipment for better reporting
        $equipmentByKey = Equipment::whereNotNull('induction_category')
            ->where('induction_category', '!=', '')
            ->get()
            ->groupBy('induction_category');
        
        foreach ($inductionKeys as $key) {
            if ($courses->has($key)) {
                $course = $courses->get($key);
                $count = Induction::where('key', $key)->whereNull('course_id')->count();
                
                if ($count > 0) {
                    $this->line("✓ Key '{$key}' matches course '{$course->name}' - {$count} inductions to update");
                    
                    if (!$isDryRun) {
                        Induction::where('key', $key)
                            ->whereNull('course_id')
                            ->update(['course_id' => $course->id]);
                    }
                    
                    $matched += $count;
                }
            } else {
                $count = Induction::where('key', $key)->count();
                $equipment = $equipmentByKey->get($key, collect())->pluck('name')->implode(', ');
                $unmatched[] = [
                    'key' => $key,
                    'count' => $count,
                    'equipment' => $equipment ?: 'No equipment found'
                ];
            }
        }
        
        // Check for equipment<->course relationships that don't match key<->slug
        $this->line('');
        $this->info('Checking for equipment/course relationships vs key matching...');
        
        $mismatchedEquipment = [];
        foreach ($equipmentByKey as $key => $equipmentList) {
            foreach ($equipmentList as $equipment) {
                $coursesByRelation = $equipment->courses;
                $courseByKey = $courses->get($key);
                
                if ($coursesByRelation->count() > 0) {
                    // Equipment has course relationships
                    if (!$courseByKey) {
                        // But no matching course by key
                        $mismatchedEquipment[] = [
                            'equipment' => $equipment->name,
                            'key' => $key,
                            'courses' => $coursesByRelation->pluck('name')->implode(', '),
                            'issue' => 'Has course relation but key does not match any course slug'
                        ];
                    } elseif (!$coursesByRelation->contains('id', $courseByKey->id)) {
                        // Course by key doesn't match course by relation
                        $mismatchedEquipment[] = [
                            'equipment' => $equipment->name,
                            'key' => $key,
                            'course_by_key' => $courseByKey->name,
                            'courses_by_relation' => $coursesByRelation->pluck('name')->implode(', '),
                            'issue' => 'Key matches different course than equipment relation'
                        ];
                    }
                }
            }
        }
        
        // Report results
        $this->line('');
        $this->info('=== SUMMARY ===');
        $this->info("Total inductions that can be matched: {$matched}");
        
        if (count($unmatched) > 0) {
            $this->line('');
            $this->warn('Unmatched induction keys (no corresponding course slug):');
            $this->table(
                ['Key', 'Induction Count', 'Equipment'],
                $unmatched
            );
        }
        
        if (count($mismatchedEquipment) > 0) {
            $this->line('');
            $this->warn('Equipment with mismatched key/course relationships:');
            foreach ($mismatchedEquipment as $mismatch) {
                $this->error("Equipment: {$mismatch['equipment']}");
                $this->line("  Key: {$mismatch['key']}");
                if (isset($mismatch['course_by_key'])) {
                    $this->line("  Course by key: {$mismatch['course_by_key']}");
                }
                if (isset($mismatch['courses_by_relation'])) {
                    $this->line("  Course(s) by relation: {$mismatch['courses_by_relation']}");
                } else {
                    $this->line("  Courses by relation: {$mismatch['courses']}");
                }
                $this->line("  Issue: {$mismatch['issue']}");
                $this->line('');
            }
        }
        
        if (!$isDryRun && $matched > 0) {
            $this->info("Updated {$matched} induction records with course_id");
        }
        
        return 0;
    }
}
