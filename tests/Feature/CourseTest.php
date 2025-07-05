<?php

namespace Tests\Feature;

use BB\Entities\Course;
use BB\Entities\Equipment;
use BB\Entities\Settings;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_view_courses_index()
    {
        // Set inductions as live for everyone
        Settings::create(['key' => 'inductions_live', 'value' => 'true']);
        
        $user = factory(User::class)->create();
        $course = factory(Course::class)->create();

        $response = $this->actingAs($user)
            ->get(route('courses.index'));

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('Courses/Index')
                ->has('courses', 1);
        });
    }

    public function test_admin_can_create_course()
    {
        $admin = factory(User::class)->state('admin')->create();
        $equipment = factory(Equipment::class)->times(3)->create();

        $courseData = [
            'name' => 'Test Course',
            'slug' => 'test-course',
            'description' => 'Test description',
            'format' => 'group',
            'format_description' => 'Group class format',
            'frequency' => 'regular',
            'frequency_description' => 'Weekly sessions',
            'wait_time' => '1-2 weeks',
            'equipment' => $equipment->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($admin)
            ->post(route('courses.store'), $courseData);

        $response->assertRedirect();
        
        $course = Course::where('slug', 'test-course')->first();
        $this->assertNotNull($course);
        $this->assertEquals('Test Course', $course->name);
        $this->assertEquals(3, $course->equipment()->count());
    }

    public function test_non_admin_cannot_create_course()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->post(route('courses.store'), [
                'name' => 'Test Course',
                'slug' => 'test-course',
            ]);

        $response->assertForbidden();
    }

    public function test_admin_can_update_course()
    {
        $admin = factory(User::class)->state('admin')->create();
        $course = factory(Course::class)->create();
        $oldEquipment = factory(Equipment::class)->times(2)->create();
        $newEquipment = factory(Equipment::class)->times(3)->create();
        
        // Attach old equipment
        $course->equipment()->attach($oldEquipment);

        $updateData = [
            'name' => 'Updated Course',
            'slug' => 'updated-course',
            'description' => 'Updated description',
            'format' => 'quiz',
            'format_description' => 'Updated format description',
            'frequency' => 'self-serve',
            'frequency_description' => 'Updated frequency description',
            'wait_time' => '2-3 weeks',
            'equipment' => $newEquipment->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($admin)
            ->put(route('courses.update', $course), $updateData);

        $response->assertRedirect();
        
        $course->refresh();
        $this->assertEquals('Updated Course', $course->name);
        $this->assertEquals('updated-course', $course->slug);
        $this->assertEquals(3, $course->equipment()->count());
        
        // Verify old equipment was detached
        foreach ($oldEquipment as $eq) {
            $this->assertFalse($course->equipment->contains($eq));
        }
    }

    public function test_slug_change_maintains_equipment_relationships()
    {
        $admin = factory(User::class)->state('admin')->create();
        $course = factory(Course::class)->create(['slug' => 'old-slug']);
        $equipment = factory(Equipment::class)->times(3)->create();
        
        $course->equipment()->attach($equipment);

        $response = $this->actingAs($admin)
            ->put(route('courses.update', $course), [
                'name' => $course->name,
                'slug' => 'new-slug',
                'description' => $course->description,
                'format' => $course->format,
                'format_description' => $course->format_description,
                'frequency' => $course->frequency,
                'frequency_description' => $course->frequency_description,
                'wait_time' => $course->wait_time,
                'equipment' => $equipment->pluck('id')->toArray(),
            ]);

        $response->assertRedirect();
        
        $course->refresh();
        $this->assertEquals('new-slug', $course->slug);
        $this->assertEquals(3, $course->equipment()->count());
    }

    public function test_admin_can_delete_course()
    {
        $admin = factory(User::class)->state('admin')->create();
        $course = factory(Course::class)->create();
        $equipment = factory(Equipment::class)->times(2)->create();
        
        $course->equipment()->attach($equipment);

        $response = $this->actingAs($admin)
            ->delete(route('courses.destroy', $course));

        $response->assertRedirect(route('courses.index'));
        
        $this->assertSoftDeleted('courses', ['id' => $course->id]);
        
        // Verify equipment relationships were detached
        $this->assertEquals(0, $course->equipment()->count());
    }

    public function test_validation_requires_unique_slug()
    {
        $admin = factory(User::class)->state('admin')->create();
        factory(Course::class)->create(['slug' => 'existing-slug']);

        $response = $this->actingAs($admin)
            ->post(route('courses.store'), [
                'name' => 'Test Course',
                'slug' => 'existing-slug',
                'format' => 'group',
                'frequency' => 'regular',
            ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_equipment_can_belong_to_multiple_courses()
    {
        $admin = factory(User::class)->state('admin')->create();
        $equipment = factory(Equipment::class)->create();
        
        $course1 = factory(Course::class)->create();
        $course2 = factory(Course::class)->create();
        
        $course1->equipment()->attach($equipment);
        $course2->equipment()->attach($equipment);
        
        $this->assertEquals(2, $equipment->courses()->count());
        $this->assertTrue($course1->equipment->contains($equipment));
        $this->assertTrue($course2->equipment->contains($equipment));
    }

    public function test_course_show_page_loads()
    {
        // Set inductions as live for everyone
        Settings::create(['key' => 'inductions_live', 'value' => 'true']);
        
        $user = factory(User::class)->create();
        $course = factory(Course::class)->create();

        $response = $this->actingAs($user)
            ->get(route('courses.show', $course));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_edit_page()
    {
        $admin = factory(User::class)->state('admin')->create();
        $course = factory(Course::class)->create();
        $equipment = factory(Equipment::class)->times(2)->create();
        $course->equipment()->attach($equipment);

        $response = $this->actingAs($admin)
            ->get(route('courses.edit', $course));

        $response->assertStatus(200);
        $response->assertInertia(function ($page) use ($course) {
            $page->component('Courses/Edit')
                ->has('course')
                ->where('course.data.id', $course->id)
                ->has('equipment')
                ->has('formatOptions')
                ->has('frequencyOptions')
                ->has('urls');
        });
    }

    public function test_non_admin_cannot_view_edit_page()
    {
        $user = factory(User::class)->create();
        $course = factory(Course::class)->create();

        $response = $this->actingAs($user)
            ->get(route('courses.edit', $course));

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_update_course()
    {
        $user = factory(User::class)->create();
        $course = factory(Course::class)->create();

        $response = $this->actingAs($user)
            ->put(route('courses.update', $course), [
                'name' => 'Updated Name',
                'slug' => $course->slug,
                'description' => $course->description,
                'format' => $course->format,
                'frequency' => $course->frequency,
            ]);

        $response->assertForbidden();
        
        $course->refresh();
        $this->assertNotEquals('Updated Name', $course->name);
    }

    public function test_non_admin_cannot_delete_course()
    {
        $user = factory(User::class)->create();
        $course = factory(Course::class)->create();

        $response = $this->actingAs($user)
            ->delete(route('courses.destroy', $course));

        $response->assertForbidden();
        
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'deleted_at' => null
        ]);
    }

    public function test_regular_user_cannot_view_courses_in_preview_mode()
    {
        // Don't set the inductions_live setting, so it's in preview mode
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->get(route('courses.index'));

        $response->assertForbidden();
    }

    public function test_area_coordinator_can_view_courses_in_preview_mode()
    {
        // Don't set the inductions_live setting, so it's in preview mode
        $user = factory(User::class)->create();
        $area = factory(\BB\Entities\EquipmentArea::class)->create();
        $user->equipmentAreas()->attach($area);

        $response = $this->actingAs($user)
            ->get(route('courses.index'));

        $response->assertStatus(200);
    }

    public function test_equipment_maintainer_can_view_courses_in_preview_mode()
    {
        // Don't set the inductions_live setting, so it's in preview mode
        $user = factory(User::class)->create();
        $maintainerGroup = factory(\BB\Entities\MaintainerGroup::class)->create();
        $user->maintainerGroups()->attach($maintainerGroup);

        $response = $this->actingAs($user)
            ->get(route('courses.index'));

        $response->assertStatus(200);
    }

    public function test_courses_index_shows_preview_alert_in_preview_mode()
    {
        // Don't set the inductions_live setting, so it's in preview mode
        $admin = factory(User::class)->state('admin')->create();

        $response = $this->actingAs($admin)
            ->get(route('courses.index'));

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('Courses/Index')
                ->where('isPreview', true);
        });
    }

    public function test_courses_index_does_not_show_preview_alert_when_live()
    {
        Settings::create(['key' => 'inductions_live', 'value' => 'true']);
        
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->get(route('courses.index'));

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('Courses/Index')
                ->where('isPreview', false);
        });
    }
}