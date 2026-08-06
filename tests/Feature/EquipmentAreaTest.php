<?php

namespace Tests\Feature;

use BB\Entities\EquipmentArea;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentAreaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return factory(User::class)->state('admin')->create();
    }

    public function test_index_renders_inertia_with_areas()
    {
        factory(EquipmentArea::class)->create();

        $this->actingAs($this->admin())->get(route('equipment_area.index'))
            ->assertStatus(200)
            ->assertInertia(function ($page) {
                $page->component('EquipmentAreas/Index')->has('areas', 1);
            });
    }

    public function test_create_and_edit_render_inertia()
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('equipment_area.create'))
            ->assertInertia(function ($page) {
                $page->component('EquipmentAreas/Create')->has('searchUrl');
            });

        $area = factory(EquipmentArea::class)->create();
        $this->actingAs($admin)->get(route('equipment_area.edit', $area))
            ->assertInertia(function ($page) use ($area) {
                $page->component('EquipmentAreas/Edit')->where('area.id', $area->id);
            });
    }

    public function test_admin_can_create_an_area_and_sync_coordinators()
    {
        $admin = $this->admin();
        $coordinators = factory(User::class, 2)->create();

        $response = $this->actingAs($admin)->post(route('equipment_area.store'), [
            'name' => 'Visual Arts',
            'description' => 'Painting and printing',
            'area_coordinators' => $coordinators->pluck('id')->all(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('equipment_areas', ['name' => 'Visual Arts', 'slug' => 'visual-arts']);
        $area = EquipmentArea::where('slug', 'visual-arts')->first();
        $this->assertEqualsCanonicalizing(
            $coordinators->pluck('id')->all(),
            $area->areaCoordinators()->pluck('users.id')->all()
        );
    }

    public function test_slug_is_generated_from_name_when_omitted()
    {
        $this->actingAs($this->admin())->post(route('equipment_area.store'), [
            'name' => 'Bike Space',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('equipment_areas', ['slug' => 'bike-space']);
    }

    public function test_update_replaces_the_coordinator_set()
    {
        $admin = $this->admin();
        $area = factory(EquipmentArea::class)->create(['name' => 'Metalwork', 'slug' => 'metalwork']);
        $original = factory(User::class)->create();
        $replacement = factory(User::class)->create();
        $area->areaCoordinators()->sync([$original->id]);

        $this->actingAs($admin)->put(route('equipment_area.update', $area), [
            'name' => 'Metalwork',
            'slug' => 'metalwork',
            'area_coordinators' => [$replacement->id],
        ])->assertRedirect();

        $this->assertEquals([$replacement->id], $area->areaCoordinators()->pluck('users.id')->all());
    }

    public function test_duplicate_name_is_rejected()
    {
        factory(EquipmentArea::class)->create(['name' => 'Woodwork', 'slug' => 'woodwork']);

        $this->actingAs($this->admin())->post(route('equipment_area.store'), [
            'name' => 'Woodwork',
            'slug' => 'woodwork-2',
        ])->assertSessionHasErrors('name');
    }

    public function test_non_admin_cannot_create_or_update_or_delete()
    {
        $user = factory(User::class)->create();
        $area = factory(EquipmentArea::class)->create();

        $this->actingAs($user)->post(route('equipment_area.store'), ['name' => 'Nope'])->assertForbidden();
        $this->actingAs($user)->put(route('equipment_area.update', $area), [
            'name' => 'Nope',
            'slug' => $area->slug,
        ])->assertForbidden();
        $this->actingAs($user)->delete(route('equipment_area.destroy', $area))->assertForbidden();
    }
}
