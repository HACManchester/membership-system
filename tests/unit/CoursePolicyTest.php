<?php

namespace Tests\Unit;

use BB\Entities\Course;
use BB\Entities\Equipment;
use BB\Entities\EquipmentArea;
use BB\Entities\MaintainerGroup;
use BB\Entities\Role;
use BB\Entities\Settings;
use BB\Entities\User;
use BB\Policies\CoursePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoursePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new CoursePolicy();
    }

    public function test_admin_can_do_everything()
    {
        $admin = factory(User::class)->create();
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['title' => 'Admin', 'description' => 'Administrator']
        );
        $admin->assignRole($adminRole);
        
        $course = factory(Course::class)->create();

        $this->assertTrue($admin->can('viewAny', Course::class));
        $this->assertTrue($admin->can('view', $course));
        $this->assertTrue($admin->can('create', Course::class));
        $this->assertTrue($admin->can('update', $course));
        $this->assertTrue($admin->can('delete', $course));
        $this->assertTrue($admin->can('restore', $course));
        $this->assertTrue($admin->can('forceDelete', $course));
    }

    public function test_all_users_can_view_courses()
    {
        // Set inductions as live for everyone
        Settings::create(['key' => 'inductions_live', 'value' => 'true']);
        
        $user = factory(User::class)->create();
        $course = factory(Course::class)->create();

        $this->assertTrue($this->policy->viewAny($user));
        $this->assertTrue($this->policy->view($user, $course));
    }

    public function test_area_coordinator_can_create_courses()
    {
        $user = factory(User::class)->create();
        $area = factory(EquipmentArea::class)->create();
        $user->equipmentAreas()->attach($area);

        $this->assertTrue($this->policy->create($user));
    }

    public function test_non_area_coordinator_cannot_create_courses()
    {
        $user = factory(User::class)->create();

        $this->assertFalse($this->policy->create($user));
    }

    public function test_equipment_maintainer_can_update_and_delete_course()
    {
        $user = factory(User::class)->create();
        $course = factory(Course::class)->create();
        $equipment = factory(Equipment::class)->create();
        $area = factory(EquipmentArea::class)->create();
        $maintainerGroup = factory(MaintainerGroup::class)->create(['equipment_area_id' => $area->id]);
        
        // Setup relationships
        $equipment->maintainerGroup()->associate($maintainerGroup);
        $equipment->save();
        $maintainerGroup->maintainers()->attach($user);
        $course->equipment()->attach($equipment);
        
        // Reload course to ensure relationships are loaded
        $course->load('equipment.maintainerGroup.maintainers', 'equipment.maintainerGroup.equipmentArea.areaCoordinators');

        $this->assertTrue($this->policy->update($user, $course));
        $this->assertTrue($this->policy->delete($user, $course));
    }

    public function test_area_coordinator_can_update_and_delete_course()
    {
        $user = factory(User::class)->create();
        $course = factory(Course::class)->create();
        $equipment = factory(Equipment::class)->create();
        $area = factory(EquipmentArea::class)->create();
        $maintainerGroup = factory(MaintainerGroup::class)->create(['equipment_area_id' => $area->id]);
        
        // Setup relationships
        $equipment->maintainerGroup()->associate($maintainerGroup);
        $equipment->save();
        $area->areaCoordinators()->attach($user);
        $course->equipment()->attach($equipment);
        
        // Reload course to ensure relationships are loaded
        $course->load('equipment.maintainerGroup.maintainers', 'equipment.maintainerGroup.equipmentArea.areaCoordinators');

        $this->assertTrue($this->policy->update($user, $course));
        $this->assertTrue($this->policy->delete($user, $course));
    }

    public function test_non_maintainer_cannot_update_or_delete_course()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $course = factory(Course::class)->create();
        $equipment = factory(Equipment::class)->create();
        $maintainerGroup = factory(MaintainerGroup::class)->create();
        
        // Setup relationships with different user
        $equipment->maintainerGroup()->associate($maintainerGroup);
        $equipment->save();
        $maintainerGroup->maintainers()->attach($otherUser);
        $course->equipment()->attach($equipment);

        $this->assertFalse($this->policy->update($user, $course));
        $this->assertFalse($this->policy->delete($user, $course));
    }

    public function test_restore_always_returns_false()
    {
        $user = factory(User::class)->create();
        $course = factory(Course::class)->create();

        $this->assertFalse($this->policy->restore($user, $course));
    }

    public function test_force_delete_always_returns_false()
    {
        $user = factory(User::class)->create();
        $course = factory(Course::class)->create();

        $this->assertFalse($this->policy->forceDelete($user, $course));
    }

    public function test_is_maintainer_or_coordinator_with_multiple_equipment()
    {
        $user = factory(User::class)->create();
        $course = factory(Course::class)->create();
        $area = factory(EquipmentArea::class)->create();
        
        // Create multiple equipment with different maintainer groups
        $equipment1 = factory(Equipment::class)->create();
        $equipment2 = factory(Equipment::class)->create();
        $maintainerGroup1 = factory(MaintainerGroup::class)->create(['equipment_area_id' => $area->id]);
        $maintainerGroup2 = factory(MaintainerGroup::class)->create(['equipment_area_id' => $area->id]);
        
        $equipment1->maintainerGroup()->associate($maintainerGroup1);
        $equipment1->save();
        $equipment2->maintainerGroup()->associate($maintainerGroup2);
        $equipment2->save();
        
        // User is maintainer of only one equipment
        $maintainerGroup1->maintainers()->attach($user);
        
        // Course has both equipment
        $course->equipment()->attach([$equipment1->id, $equipment2->id]);
        
        // Reload course to ensure relationships are loaded
        $course->load('equipment.maintainerGroup.maintainers', 'equipment.maintainerGroup.equipmentArea.areaCoordinators');

        // User should still be able to update/delete since they maintain at least one equipment
        $this->assertTrue($this->policy->update($user, $course));
        $this->assertTrue($this->policy->delete($user, $course));
    }

    public function test_regular_user_cannot_view_courses_in_preview_mode()
    {
        // Don't set inductions_live, so it's in preview mode by default
        $user = factory(User::class)->create();

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_area_coordinator_can_view_courses_in_preview_mode()
    {
        // Don't set inductions_live, so it's in preview mode by default
        $user = factory(User::class)->create();
        $area = factory(EquipmentArea::class)->create();
        $user->equipmentAreas()->attach($area);

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_equipment_maintainer_can_view_courses_in_preview_mode()
    {
        // Don't set inductions_live, so it's in preview mode by default
        $user = factory(User::class)->create();
        $maintainerGroup = factory(MaintainerGroup::class)->create();
        $user->maintainerGroups()->attach($maintainerGroup);

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_all_users_can_view_courses_when_live()
    {
        // Set inductions as live for everyone
        Settings::create(['key' => 'inductions_live', 'value' => 'true']);
        
        $user = factory(User::class)->create();

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_regular_user_cannot_view_courses_when_setting_is_false()
    {
        // Explicitly set inductions_live to false
        Settings::create(['key' => 'inductions_live', 'value' => 'false']);
        
        $user = factory(User::class)->create();

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_privileged_users_can_view_courses_when_setting_is_false()
    {
        // Explicitly set inductions_live to false
        Settings::create(['key' => 'inductions_live', 'value' => 'false']);
        
        $user = factory(User::class)->create();
        $area = factory(EquipmentArea::class)->create();
        $user->equipmentAreas()->attach($area);

        $this->assertTrue($this->policy->viewAny($user));
    }
}