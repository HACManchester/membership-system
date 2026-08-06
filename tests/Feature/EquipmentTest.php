<?php

namespace Tests\Feature;

use BB\Entities\Course;
use BB\Entities\Equipment;
use BB\Entities\EquipmentArea;
use BB\Entities\TrainingRecord;
use BB\Entities\MaintainerGroup;
use BB\Entities\Role;
use BB\Entities\Room;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $equipmentRoleUser;
    protected $regularUser;
    protected $maintainerUser;
    protected $areaCoordinatorUser;
    protected $trainerUser;
    protected $equipment;
    protected $equipmentWithAccessCode;
    protected $maintainerGroup;
    protected $equipmentArea;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTestData();
    }

    protected function setUpTestData(): void
    {
        // Create users with different roles
        $this->admin = factory(User::class)->state('admin')->create();
        $this->equipmentRoleUser = factory(User::class)->create();
        
        // Create equipment role and assign to user
        $equipmentRole = Role::firstOrCreate(
            ['name' => 'equipment'],
            ['title' => 'Equipment Manager']
        );
        $this->equipmentRoleUser->assignRole($equipmentRole);

        $this->regularUser = factory(User::class)->create();
        $this->maintainerUser = factory(User::class)->create();
        $this->areaCoordinatorUser = factory(User::class)->create();
        $this->trainerUser = factory(User::class)->create();

        // Create equipment area and maintainer group
        $this->equipmentArea = factory(EquipmentArea::class)->create([
            'name' => 'Test Area',
            'slug' => 'test-area'
        ]);

        $this->maintainerGroup = factory(MaintainerGroup::class)->create([
            'name' => 'Test Maintainers',
            'slug' => 'test-maintainers',
            'equipment_area_id' => $this->equipmentArea->id,
        ]);

        // Create equipment
        $this->equipment = factory(Equipment::class)->create([
            'name' => 'Test Equipment',
            'slug' => 'test-equipment',
            'requires_induction' => true,
            'induction_category' => 'test-equipment',
            'accepting_inductions' => true,
            'maintainer_group_id' => $this->maintainerGroup->id,
        ]);

        $this->equipmentWithAccessCode = factory(Equipment::class)->create([
            'name' => 'Secure Equipment',
            'slug' => 'secure-equipment',
            'requires_induction' => true,
            'induction_category' => 'secure-equipment',
            'accepting_inductions' => true,
            'access_code' => 'SECRET123',
        ]);

        // Set up relationships
        $this->maintainerUser->maintainerGroups()->attach($this->maintainerGroup);
        $this->areaCoordinatorUser->equipmentAreas()->attach($this->equipmentArea);

        // Create trainer induction
        $trainerTrainingRecord = new TrainingRecord([
            'key' => 'test-equipment',
            'user_id' => $this->trainerUser->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => true,
            'trainer_user_id' => $this->admin->id,
        ]);
        $trainerTrainingRecord->save();
    }

    /** @test */
    public function anyone_can_view_equipment_index()
    {
        $response = $this->actingAs($this->regularUser)->get(route('equipment.index'));
        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('Equipment/Index')->has('equipment');
        });
    }

    /** @test */
    public function equipment_index_exposes_room_display_from_the_related_room()
    {
        $room = factory(Room::class)->create(['name' => 'The Workshop', 'slug' => 'the-workshop']);
        $equipment = factory(Equipment::class)->create(['room_id' => $room->id]);

        $response = $this->actingAs($this->regularUser)->get(route('equipment.index'));

        $items = collect($response->viewData('page')['props']['equipment']);
        $item = $items->firstWhere('id', $equipment->id);
        $this->assertSame('The Workshop', $item['room_display']);
    }

    /** @test */
    public function create_and_edit_pages_render_inertia()
    {
        $this->actingAs($this->admin)->get(route('equipment.create'))
            ->assertInertia(function ($page) {
                $page->component('Equipment/Create')->has('rooms');
            });

        $this->actingAs($this->admin)->get(route('equipment.edit', $this->equipment))
            ->assertInertia(function ($page) {
                $page->component('Equipment/Edit')->where('equipment.slug', $this->equipment->slug);
            });
    }

    /** @test */
    public function admin_can_create_equipment_with_room()
    {
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);

        $response = $this->actingAs($this->admin)->post(route('equipment.store'), [
            'name' => 'New Drill',
            'room_id' => $room->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('equipment', [
            'slug' => 'new-drill',
            'room_id' => $room->id,
        ]);
    }

    /** @test */
    public function slug_is_generated_from_name_when_omitted()
    {
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);

        $this->actingAs($this->admin)->post(route('equipment.store'), [
            'name' => 'Big Lathe',
            'room_id' => $room->id,
        ]);

        $this->assertDatabaseHas('equipment', ['slug' => 'big-lathe']);
    }

    /** @test */
    public function empty_optional_foreign_keys_are_stored_as_null()
    {
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);

        $this->actingAs($this->admin)->post(route('equipment.store'), [
            'name' => 'Sparse Tool',
            'room_id' => $room->id,
            'maintainer_group_id' => '',
            'permaloan_user_id' => '',
        ]);

        $this->assertDatabaseHas('equipment', [
            'slug' => 'sparse-tool',
            'maintainer_group_id' => null,
            'permaloan_user_id' => null,
        ]);
    }

    /** @test */
    public function usage_cost_is_no_longer_required()
    {
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);

        $response = $this->actingAs($this->admin)->post(route('equipment.store'), [
            'name' => 'Free Tool',
            'room_id' => $room->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('equipment', ['slug' => 'free-tool']);
    }

    /** @test */
    public function anyone_can_view_equipment_show()
    {
        $response = $this->actingAs($this->regularUser)->get(route('equipment.show', $this->equipment));
        $response->assertStatus(200);
        $response->assertViewHas('equipment', $this->equipment);
    }

    /** @test */
    public function admin_can_create_equipment()
    {
        $response = $this->actingAs($this->admin)->get(route('equipment.create'));
        $response->assertStatus(200);
    }

    /** @test */
    public function equipment_role_user_can_create_equipment()
    {
        $response = $this->actingAs($this->equipmentRoleUser)->get(route('equipment.create'));
        $response->assertStatus(200);
    }

    /** @test */
    public function maintainer_can_create_equipment()
    {
        $response = $this->actingAs($this->maintainerUser)->get(route('equipment.create'));
        $response->assertStatus(200);
    }

    /** @test */
    public function area_coordinator_can_create_equipment()
    {
        $response = $this->actingAs($this->areaCoordinatorUser)->get(route('equipment.create'));
        $response->assertStatus(200);
    }

    /** @test */
    public function regular_user_cannot_create_equipment()
    {
        $response = $this->actingAs($this->regularUser)->get(route('equipment.create'));
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_edit_equipment()
    {
        $response = $this->actingAs($this->admin)->get(route('equipment.edit', $this->equipment));
        $response->assertStatus(200);
    }

    /** @test */
    public function equipment_role_user_can_edit_equipment()
    {
        $response = $this->actingAs($this->equipmentRoleUser)->get(route('equipment.edit', $this->equipment));
        $response->assertStatus(200);
    }

    /** @test */
    public function maintainer_can_edit_their_equipment()
    {
        $response = $this->actingAs($this->maintainerUser)->get(route('equipment.edit', $this->equipment));
        $response->assertStatus(200);
    }

    /** @test */
    public function area_coordinator_can_edit_equipment_in_their_area()
    {
        $response = $this->actingAs($this->areaCoordinatorUser)->get(route('equipment.edit', $this->equipment));
        $response->assertStatus(200);
    }

    /** @test */
    public function regular_user_cannot_edit_equipment()
    {
        $response = $this->actingAs($this->regularUser)->get(route('equipment.edit', $this->equipment));
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_delete_equipment()
    {
        $response = $this->actingAs($this->admin)->delete(route('equipment.destroy', $this->equipment));
        $response->assertRedirect(route('equipment.index'));
        $this->assertSoftDeleted('equipment', ['id' => $this->equipment->id]);
    }

    /** @test */
    public function regular_user_cannot_delete_equipment()
    {
        $response = $this->actingAs($this->regularUser)->delete(route('equipment.destroy', $this->equipment));
        $response->assertStatus(403);
    }

    /** @test */
    public function access_code_not_visible_to_untrained_users()
    {
        $response = $this->actingAs($this->regularUser)->get(route('equipment.show', $this->equipmentWithAccessCode));
        $response->assertStatus(200);
        $response->assertDontSee('SECRET123');
        $response->assertDontSee('Access code');
    }

    /** @test */
    public function access_code_visible_to_trained_users()
    {
        // Create a trained user
        $trainedUser = factory(User::class)->create();
        $trainingRecord = new TrainingRecord([
            'key' => 'secure-equipment',
            'user_id' => $trainedUser->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => false,
            'trainer_user_id' => $this->admin->id,
        ]);
        $trainingRecord->save();

        $response = $this->actingAs($trainedUser)->get(route('equipment.show', $this->equipmentWithAccessCode));
        $response->assertStatus(200);
        $response->assertSee('SECRET123');
        $response->assertSee('Access code');
    }

    /** @test */
    public function access_code_visible_to_trainers()
    {
        // Create trainer for secure equipment
        $trainer = factory(User::class)->create();
        $trainerTrainingRecord = new TrainingRecord([
            'key' => 'secure-equipment',
            'user_id' => $trainer->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => true,
            'trainer_user_id' => $this->admin->id,
        ]);
        $trainerTrainingRecord->save();

        $response = $this->actingAs($trainer)->get(route('equipment.show', $this->equipmentWithAccessCode));
        $response->assertStatus(200);
        $response->assertSee('SECRET123');
        $response->assertSee('Access code');
    }

    /** @test */
    public function access_code_visible_in_equipment_index_for_trained_users()
    {
        // Create a trained user
        $trainedUser = factory(User::class)->create();
        $trainingRecord = new TrainingRecord([
            'key' => 'secure-equipment',
            'user_id' => $trainedUser->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => false,
            'trainer_user_id' => $this->admin->id,
        ]);
        $trainingRecord->save();

        $response = $this->actingAs($trainedUser)->get(route('equipment.index'));
        $response->assertStatus(200);

        $equipment = collect($response->viewData('page')['props']['equipment']);
        $this->assertTrue(
            $equipment->contains(function ($item) {
                return ($item['access_code'] ?? null) === 'SECRET123';
            })
        );
    }

    /** @test */
    public function access_code_not_visible_in_equipment_index_for_untrained_users()
    {
        $response = $this->actingAs($this->regularUser)->get(route('equipment.index'));
        $response->assertStatus(200);

        $equipment = collect($response->viewData('page')['props']['equipment']);
        $this->assertFalse(
            $equipment->contains(function ($item) {
                return array_key_exists('access_code', $item);
            })
        );
    }

    /** @test */
    public function trainer_can_request_induction_for_others()
    {
        $response = $this->actingAs($this->trainerUser)
            ->post(route('equipment_training.create', $this->equipment), [
                'user_id' => $this->regularUser->id,
            ]);

        $response->assertRedirect(route('equipment.show', $this->equipment));
        $this->assertDatabaseHas('inductions', [
            'user_id' => $this->regularUser->id,
            'key' => 'test-equipment',
        ]);
    }

    /** @test */
    public function regular_user_can_request_own_induction()
    {
        $response = $this->actingAs($this->regularUser)
            ->post(route('equipment_training.create', $this->equipment));

        $response->assertRedirect(route('equipment.show', $this->equipment));
        $this->assertDatabaseHas('inductions', [
            'user_id' => $this->regularUser->id,
            'key' => 'test-equipment',
        ]);
    }

    /** @test */
    public function trainer_can_mark_user_as_trained()
    {
        // Create pending induction
        $pendingTrainingRecord = new TrainingRecord([
            'key' => 'test-equipment',
            'user_id' => $this->regularUser->id,
            'trained' => null,
            'active' => false,
            'is_trainer' => false,
            'trainer_user_id' => null,
        ]);
        $pendingTrainingRecord->save();

        $response = $this->actingAs($this->trainerUser)
            ->post(route('equipment_training.train', [$this->equipment, $pendingTrainingRecord]), [
                'trainer_user_id' => $this->trainerUser->id,
            ]);

        $response->assertRedirect(route('equipment.show', $this->equipment));
        $this->assertDatabaseHas('inductions', [
            'id' => $pendingTrainingRecord->id,
            'trainer_user_id' => $this->trainerUser->id,
        ]);
        
        $pendingTrainingRecord->refresh();
        $this->assertNotNull($pendingTrainingRecord->trained);
    }

    /** @test */
    public function regular_user_cannot_mark_user_as_trained()
    {
        // Create pending induction
        $pendingTrainingRecord = new TrainingRecord([
            'key' => 'test-equipment',
            'user_id' => $this->regularUser->id,
            'trained' => null,
            'active' => false,
            'is_trainer' => false,
            'trainer_user_id' => null,
        ]);
        $pendingTrainingRecord->save();

        $anotherUser = factory(User::class)->create();
        $response = $this->actingAs($anotherUser)
            ->post(route('equipment_training.train', [$this->equipment, $pendingTrainingRecord]), [
                'trainer_user_id' => $anotherUser->id,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_all_equipment_features()
    {
        $response = $this->actingAs($this->admin)->get(route('equipment.show', $this->equipmentWithAccessCode));
        $response->assertStatus(200);
        $response->assertSee('Edit');
        $response->assertSee('Delete');
    }

    /** @test */
    public function equipment_without_access_code_works_normally()
    {
        $equipmentWithoutCode = factory(Equipment::class)->create([
            'name' => 'Simple Equipment',
            'slug' => 'simple-equipment',
            'requires_induction' => false,
            'access_code' => null,
        ]);

        $response = $this->actingAs($this->regularUser)->get(route('equipment.show', $equipmentWithoutCode));
        $response->assertStatus(200);
        $response->assertDontSee('Access code');
    }

    /** @test */
    public function pending_induction_shows_appropriate_status()
    {
        // Create pending induction
        $pendingTrainingRecord = new TrainingRecord([
            'key' => 'test-equipment',
            'user_id' => $this->regularUser->id,
            'trained' => null,
            'active' => false,
            'is_trainer' => false,
            'trainer_user_id' => null,
        ]);
        $pendingTrainingRecord->save();

        $response = $this->actingAs($this->regularUser)->get(route('equipment.show', $this->equipment));
        $response->assertStatus(200);
        $response->assertSee('Training to be completed');
    }

    /** @test */
    public function completed_induction_shows_appropriate_status()
    {
        // Create completed induction
        $completedTrainingRecord = new TrainingRecord([
            'key' => 'test-equipment',
            'user_id' => $this->regularUser->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => false,
            'trainer_user_id' => $this->admin->id,
        ]);
        $completedTrainingRecord->save();

        $response = $this->actingAs($this->regularUser)->get(route('equipment.show', $this->equipment));
        $response->assertStatus(200);
        $response->assertSee('You have been inducted and can use this equipment');
    }

    /** @test */
    public function a_course_managed_item_without_a_legacy_category_can_be_edited()
    {
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);
        $course = factory(Course::class)->create();
        $equipment = factory(Equipment::class)->create([
            'name' => 'Course Item',
            'slug' => 'course-item',
            'room_id' => $room->id,
            'requires_induction' => true,
            'induction_category' => null,
        ]);
        $equipment->courses()->attach($course->id);

        $response = $this->actingAs($this->admin)->put(route('equipment.update', $equipment), [
            'name' => 'Course Item Renamed',
            'slug' => 'course-item',
            'room_id' => $room->id,
            'course_id' => $course->id,
            'requires_induction' => true,
            'accepting_inductions' => false,
            'induction_category' => '',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('equipment.show', 'course-item'));
        $this->assertDatabaseHas('equipment', ['slug' => 'course-item', 'name' => 'Course Item Renamed']);
    }

    /** @test */
    public function a_non_induction_item_with_a_stale_legacy_category_can_be_edited()
    {
        // A hidden legacy category that predates alpha_dash validation must not
        // silently block editing an item that no longer requires an induction.
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);
        $equipment = factory(Equipment::class)->create([
            'name' => 'Bandsaw',
            'slug' => 'bandsaw',
            'room_id' => $room->id,
            'requires_induction' => false,
            'induction_category' => 'band saw',
        ]);

        $response = $this->actingAs($this->admin)->put(route('equipment.update', $equipment), [
            'name' => 'Bandsaw Renamed',
            'slug' => 'bandsaw',
            'room_id' => $room->id,
            'requires_induction' => false,
            'induction_category' => 'band saw',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('equipment.show', 'bandsaw'));
        $this->assertDatabaseHas('equipment', ['slug' => 'bandsaw', 'name' => 'Bandsaw Renamed']);
    }
    /** @test */
    public function a_non_permaloan_item_ignores_a_stale_permaloan_holder()
    {
        // A former holder who has since left leaves a now-invalid id on a hidden
        // field; switching off permaloan must not be blocked by it, and it must be
        // cleared rather than stored.
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);
        $holder = factory(User::class)->create();
        $equipment = factory(Equipment::class)->create([
            'name' => 'Drill',
            'slug' => 'drill',
            'room_id' => $room->id,
            'permaloan' => true,
            'permaloan_user_id' => $holder->id,
        ]);
        $holder->delete();

        $response = $this->actingAs($this->admin)->put(route('equipment.update', $equipment), [
            'name' => 'Drill',
            'slug' => 'drill',
            'room_id' => $room->id,
            'permaloan' => false,
            'permaloan_user_id' => $holder->id,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('equipment.show', 'drill'));
        $this->assertDatabaseHas('equipment', [
            'slug' => 'drill',
            'permaloan' => false,
            'permaloan_user_id' => null,
        ]);
    }
    /** @test */
    public function the_update_policy_handles_equipment_without_a_maintainer_group()
    {
        // A group-less item has no maintainer/area to manage it, so a maintainer of
        // some other group is simply denied — without tripping over the null group.
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);
        $equipment = factory(Equipment::class)->create([
            'name' => 'Loose Tool',
            'slug' => 'loose-tool',
            'room_id' => $room->id,
            'maintainer_group_id' => null,
        ]);

        $this->assertFalse($this->maintainerUser->can('update', $equipment));
        $this->assertFalse($this->maintainerUser->can('delete', $equipment));
        $this->assertTrue($this->admin->can('update', $equipment));
    }

    /** @test */
    public function a_course_less_item_that_still_flags_requires_induction_can_be_edited()
    {
        // e.g. a course was detached, leaving requires_induction set with no legacy
        // category — the (hidden) legacy fields must not block the save.
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);
        $equipment = factory(Equipment::class)->create([
            'name' => 'Orphan',
            'slug' => 'orphan',
            'room_id' => $room->id,
            'requires_induction' => true,
            'induction_category' => null,
        ]);

        $response = $this->actingAs($this->admin)->put(route('equipment.update', $equipment), [
            'name' => 'Orphan Renamed',
            'slug' => 'orphan',
            'room_id' => $room->id,
            'requires_induction' => true,
            'induction_category' => '',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('equipment.show', 'orphan'));
        $this->assertDatabaseHas('equipment', ['slug' => 'orphan', 'name' => 'Orphan Renamed']);
    }


    /** @test */
    public function updating_the_slug_redirects_to_the_new_slug()
    {
        $room = factory(Room::class)->create(['name' => 'Workshop', 'slug' => 'workshop']);
        $equipment = factory(Equipment::class)->create([
            'name' => 'Old Name',
            'slug' => 'old-slug',
            'room_id' => $room->id,
        ]);

        $response = $this->actingAs($this->admin)->put(route('equipment.update', $equipment), [
            'name' => 'New Name',
            'slug' => 'new-slug',
            'room_id' => $room->id,
        ]);

        $response->assertRedirect(route('equipment.show', 'new-slug'));
        $this->assertDatabaseHas('equipment', ['id' => $equipment->id, 'slug' => 'new-slug']);
        // the redirect target must actually resolve, not 404
        $this->actingAs($this->admin)->get(route('equipment.show', 'new-slug'))->assertStatus(200);
    }

}
