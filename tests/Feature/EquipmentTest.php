<?php

namespace Tests\Feature;

use BB\Entities\Equipment;
use BB\Entities\EquipmentArea;
use BB\Entities\Induction;
use BB\Entities\MaintainerGroup;
use BB\Entities\Role;
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
        $trainerInduction = new Induction([
            'key' => 'test-equipment',
            'user_id' => $this->trainerUser->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => true,
            'trainer_user_id' => $this->admin->id,
        ]);
        $trainerInduction->save();
    }

    /** @test */
    public function anyone_can_view_equipment_index()
    {
        $response = $this->actingAs($this->regularUser)->get(route('equipment.index'));
        $response->assertStatus(200);
        $response->assertViewHas('equipmentByRoom');
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
        $induction = new Induction([
            'key' => 'secure-equipment',
            'user_id' => $trainedUser->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => false,
            'trainer_user_id' => $this->admin->id,
        ]);
        $induction->save();

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
        $trainerInduction = new Induction([
            'key' => 'secure-equipment',
            'user_id' => $trainer->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => true,
            'trainer_user_id' => $this->admin->id,
        ]);
        $trainerInduction->save();

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
        $induction = new Induction([
            'key' => 'secure-equipment',
            'user_id' => $trainedUser->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => false,
            'trainer_user_id' => $this->admin->id,
        ]);
        $induction->save();

        $response = $this->actingAs($trainedUser)->get(route('equipment.index'));
        $response->assertStatus(200);
        $response->assertSee('SECRET123');
        $response->assertSee('Access Code');
    }

    /** @test */
    public function access_code_not_visible_in_equipment_index_for_untrained_users()
    {
        $response = $this->actingAs($this->regularUser)->get(route('equipment.index'));
        $response->assertStatus(200);
        $response->assertDontSee('SECRET123');
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
        $pendingInduction = new Induction([
            'key' => 'test-equipment',
            'user_id' => $this->regularUser->id,
            'trained' => null,
            'active' => false,
            'is_trainer' => false,
            'trainer_user_id' => null,
        ]);
        $pendingInduction->save();

        $response = $this->actingAs($this->trainerUser)
            ->post(route('equipment_training.train', [$this->equipment, $pendingInduction]), [
                'trainer_user_id' => $this->trainerUser->id,
            ]);

        $response->assertRedirect(route('equipment.show', $this->equipment));
        $this->assertDatabaseHas('inductions', [
            'id' => $pendingInduction->id,
            'trainer_user_id' => $this->trainerUser->id,
        ]);
        
        $pendingInduction->refresh();
        $this->assertNotNull($pendingInduction->trained);
    }

    /** @test */
    public function regular_user_cannot_mark_user_as_trained()
    {
        // Create pending induction
        $pendingInduction = new Induction([
            'key' => 'test-equipment',
            'user_id' => $this->regularUser->id,
            'trained' => null,
            'active' => false,
            'is_trainer' => false,
            'trainer_user_id' => null,
        ]);
        $pendingInduction->save();

        $anotherUser = factory(User::class)->create();
        $response = $this->actingAs($anotherUser)
            ->post(route('equipment_training.train', [$this->equipment, $pendingInduction]), [
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
        $pendingInduction = new Induction([
            'key' => 'test-equipment',
            'user_id' => $this->regularUser->id,
            'trained' => null,
            'active' => false,
            'is_trainer' => false,
            'trainer_user_id' => null,
        ]);
        $pendingInduction->save();

        $response = $this->actingAs($this->regularUser)->get(route('equipment.show', $this->equipment));
        $response->assertStatus(200);
        $response->assertSee('Training to be completed');
    }

    /** @test */
    public function completed_induction_shows_appropriate_status()
    {
        // Create completed induction
        $completedInduction = new Induction([
            'key' => 'test-equipment',
            'user_id' => $this->regularUser->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => false,
            'trainer_user_id' => $this->admin->id,
        ]);
        $completedInduction->save();

        $response = $this->actingAs($this->regularUser)->get(route('equipment.show', $this->equipment));
        $response->assertStatus(200);
        $response->assertSee('You have been inducted and can use this equipment');
    }
}