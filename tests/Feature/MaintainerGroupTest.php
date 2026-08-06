<?php

namespace Tests\Feature;

use BB\Entities\EquipmentArea;
use BB\Entities\MaintainerGroup;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintainerGroupTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return factory(User::class)->state('admin')->create();
    }

    public function test_index_renders_inertia()
    {
        factory(MaintainerGroup::class)->create([
            'equipment_area_id' => factory(EquipmentArea::class)->create()->id,
        ]);

        $this->actingAs($this->admin())->get(route('maintainer_groups.index'))
            ->assertStatus(200)
            ->assertInertia(function ($page) {
                $page->component('MaintainerGroups/Index')->has('maintainerGroups', 1);
            });
    }

    public function test_create_and_edit_render_inertia()
    {
        $admin = $this->admin();
        $area = factory(EquipmentArea::class)->create();

        $this->actingAs($admin)->get(route('maintainer_groups.create'))
            ->assertInertia(function ($page) {
                $page->component('MaintainerGroups/Create')->has('equipmentAreaOptions')->has('searchUrl');
            });

        $group = factory(MaintainerGroup::class)->create(['equipment_area_id' => $area->id]);
        $this->actingAs($admin)->get(route('maintainer_groups.edit', $group))
            ->assertInertia(function ($page) use ($group) {
                $page->component('MaintainerGroups/Edit')->where('maintainerGroup.id', $group->id);
            });
    }

    public function test_admin_can_create_a_group_with_area_and_maintainers()
    {
        $admin = $this->admin();
        $area = factory(EquipmentArea::class)->create();
        $maintainers = factory(User::class, 2)->create();

        $response = $this->actingAs($admin)->post(route('maintainer_groups.store'), [
            'name' => 'Woodwork',
            'equipment_area_id' => $area->id,
            'maintainers' => $maintainers->pluck('id')->all(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('maintainer_groups', [
            'name' => 'Woodwork',
            'slug' => 'woodwork',
            'equipment_area_id' => $area->id,
        ]);
        $group = MaintainerGroup::where('slug', 'woodwork')->first();
        $this->assertEqualsCanonicalizing(
            $maintainers->pluck('id')->all(),
            $group->maintainers()->pluck('users.id')->all()
        );
    }

    public function test_equipment_area_is_required()
    {
        $this->actingAs($this->admin())->post(route('maintainer_groups.store'), [
            'name' => 'No Area',
        ])->assertSessionHasErrors('equipment_area_id');
    }

    public function test_update_replaces_maintainers_and_area()
    {
        $admin = $this->admin();
        $areaA = factory(EquipmentArea::class)->create();
        $areaB = factory(EquipmentArea::class)->create();
        $group = factory(MaintainerGroup::class)->create([
            'name' => 'Metalwork',
            'slug' => 'metalwork',
            'equipment_area_id' => $areaA->id,
        ]);
        $original = factory(User::class)->create();
        $replacement = factory(User::class)->create();
        $group->maintainers()->sync([$original->id]);

        $this->actingAs($admin)->put(route('maintainer_groups.update', $group), [
            'name' => 'Metalwork',
            'slug' => 'metalwork',
            'equipment_area_id' => $areaB->id,
            'maintainers' => [$replacement->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('maintainer_groups', ['id' => $group->id, 'equipment_area_id' => $areaB->id]);
        $this->assertEquals([$replacement->id], $group->maintainers()->pluck('users.id')->all());
    }

    public function test_area_coordinator_can_manage_but_plain_member_cannot()
    {
        $area = factory(EquipmentArea::class)->create();

        $coordinator = factory(User::class)->create();
        $coordinator->equipmentAreas()->attach($area);

        // Area coordinator may create.
        $this->actingAs($coordinator)->post(route('maintainer_groups.store'), [
            'name' => 'Coordinated',
            'equipment_area_id' => $area->id,
        ])->assertRedirect();

        // Plain member may not.
        $member = factory(User::class)->create();
        $this->actingAs($member)->post(route('maintainer_groups.store'), [
            'name' => 'Nope',
            'equipment_area_id' => $area->id,
        ])->assertForbidden();
    }
}
