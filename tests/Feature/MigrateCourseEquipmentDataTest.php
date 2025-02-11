<?php

namespace Tests\Feature;

use BB\Entities\Course;
use BB\Entities\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MigrateCourseEquipmentDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrates_equipment_with_matching_course_slugs()
    {
        // Create courses
        $course1 = factory(Course::class)->create(['slug' => 'woodworking']);
        $course2 = factory(Course::class)->create(['slug' => 'metalworking']);

        // Create equipment with induction_category matching course slugs
        $equipment1 = factory(Equipment::class)->create(['induction_category' => 'woodworking']);
        $equipment2 = factory(Equipment::class)->create(['induction_category' => 'metalworking']);
        $equipment3 = factory(Equipment::class)->create(['induction_category' => 'woodworking']);

        // Run the migration command
        $this->artisan('courses:migrate-equipment-data')
            ->expectsOutput('Migration complete!')
            ->expectsOutput('Migrated: 3')
            ->expectsOutput('Skipped (already exists): 0')
            ->expectsOutput('Not found (no matching course): 0')
            ->assertExitCode(0);

        // Verify relationships were created
        $this->assertTrue($course1->equipment->contains($equipment1));
        $this->assertTrue($course2->equipment->contains($equipment2));
        $this->assertTrue($course1->equipment->contains($equipment3));
        
        // Verify pivot table has correct data
        $this->assertDatabaseHas('course_equipment', [
            'course_id' => $course1->id,
            'equipment_id' => $equipment1->id,
        ]);
        $this->assertDatabaseHas('course_equipment', [
            'course_id' => $course2->id,
            'equipment_id' => $equipment2->id,
        ]);
        $this->assertDatabaseHas('course_equipment', [
            'course_id' => $course1->id,
            'equipment_id' => $equipment3->id,
        ]);
    }

    public function test_skips_equipment_without_induction_category()
    {
        $course = factory(Course::class)->create(['slug' => 'test-course']);
        
        // Create equipment without induction_category
        factory(Equipment::class)->create(['induction_category' => '']);
        factory(Equipment::class)->create(['induction_category' => null]);

        $this->artisan('courses:migrate-equipment-data')
            ->expectsOutput('No equipment with induction_category found.')
            ->assertExitCode(0);

        // Verify no relationships were created
        $this->assertEquals(0, $course->equipment()->count());
    }

    public function test_handles_equipment_with_non_matching_course_slugs()
    {
        $course = factory(Course::class)->create(['slug' => 'existing-course']);
        
        // Create equipment with induction_category that doesn't match any course
        factory(Equipment::class)->create(['induction_category' => 'non-existent-course']);

        $this->artisan('courses:migrate-equipment-data')
            ->expectsOutput('Migration complete!')
            ->expectsOutput('Migrated: 0')
            ->expectsOutput('Skipped (already exists): 0')
            ->expectsOutput('Not found (no matching course): 1')
            ->assertExitCode(0);

        // Verify no relationships were created
        $this->assertEquals(0, $course->equipment()->count());
    }

    public function test_skips_existing_relationships()
    {
        $course = factory(Course::class)->create(['slug' => 'test-course']);
        $equipment = factory(Equipment::class)->create(['induction_category' => 'test-course']);

        // Manually create the relationship first
        $course->equipment()->attach($equipment);

        $this->artisan('courses:migrate-equipment-data')
            ->expectsOutput('Migration complete!')
            ->expectsOutput('Migrated: 0')
            ->expectsOutput('Skipped (already exists): 1')
            ->expectsOutput('Not found (no matching course): 0')
            ->assertExitCode(0);

        // Verify only one relationship exists (no duplicates)
        $this->assertEquals(1, $course->equipment()->count());
        $this->assertEquals(1, DB::table('course_equipment')->count());
    }

    public function test_dry_run_mode()
    {
        $course = factory(Course::class)->create(['slug' => 'test-course']);
        $equipment = factory(Equipment::class)->create(['induction_category' => 'test-course']);

        $this->artisan('courses:migrate-equipment-data --dry-run')
            ->expectsOutput('Running in dry-run mode - no changes will be made')
            ->expectsOutput('Migration complete!')
            ->expectsOutput('Migrated: 1')
            ->expectsOutput('This was a dry run. Run without --dry-run to apply changes.')
            ->assertExitCode(0);

        // Verify no relationships were actually created
        $this->assertEquals(0, $course->equipment()->count());
        $this->assertEquals(0, DB::table('course_equipment')->count());
    }

    public function test_handles_mixed_scenarios()
    {
        // Create courses
        $course1 = factory(Course::class)->create(['slug' => 'course-1']);
        $course2 = factory(Course::class)->create(['slug' => 'course-2']);

        // Create equipment with various scenarios
        $equipment1 = factory(Equipment::class)->create(['induction_category' => 'course-1']); // Will migrate
        $equipment2 = factory(Equipment::class)->create(['induction_category' => 'course-2']); // Will migrate
        $equipment3 = factory(Equipment::class)->create(['induction_category' => 'non-existent']); // Won't find course
        $equipment4 = factory(Equipment::class)->create(['induction_category' => 'course-1']); // Will migrate
        $equipment5 = factory(Equipment::class)->create(['induction_category' => '']); // Will be ignored (no induction_category)

        // Pre-create one relationship
        $course2->equipment()->attach($equipment2);

        $this->artisan('courses:migrate-equipment-data')
            ->expectsOutput('Migration complete!')
            ->expectsOutput('Migrated: 2') // equipment1 and equipment4
            ->expectsOutput('Skipped (already exists): 1') // equipment2
            ->expectsOutput('Not found (no matching course): 1') // equipment3
            ->assertExitCode(0);

        // Verify final state
        $this->assertEquals(2, $course1->equipment()->count()); // equipment1 and equipment4
        $this->assertEquals(1, $course2->equipment()->count()); // equipment2
        $this->assertEquals(3, DB::table('course_equipment')->count()); // Total relationships
    }

    public function test_command_includes_timestamps_in_pivot_data()
    {
        $course = factory(Course::class)->create(['slug' => 'test-course']);
        $equipment = factory(Equipment::class)->create(['induction_category' => 'test-course']);

        $this->artisan('courses:migrate-equipment-data');

        // Verify pivot table includes timestamps
        $pivot = DB::table('course_equipment')
            ->where('course_id', $course->id)
            ->where('equipment_id', $equipment->id)
            ->first();

        $this->assertNotNull($pivot);
        $this->assertNotNull($pivot->created_at);
        $this->assertNotNull($pivot->updated_at);
    }
}