<?php

namespace Tests\Unit;

use BB\Entities\MaintainerGroup;
use BB\Entities\Role;
use BB\Entities\Room;
use BB\Entities\User;
use BB\Policies\RoomPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new RoomPolicy();
    }

    public function test_admin_can_do_everything()
    {
        $admin = factory(User::class)->create();
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['title' => 'Admin', 'description' => 'Administrator']
        );
        $admin->assignRole($adminRole);

        $room = factory(Room::class)->create();

        $this->assertTrue($admin->can('viewAny', Room::class));
        $this->assertTrue($admin->can('view', $room));
        $this->assertTrue($admin->can('create', Room::class));
        $this->assertTrue($admin->can('update', $room));
        $this->assertTrue($admin->can('delete', $room));
    }

    public function test_all_users_can_view_rooms()
    {
        $user = factory(User::class)->create();
        $room = factory(Room::class)->create();

        $this->assertTrue($this->policy->viewAny($user));
        $this->assertTrue($this->policy->view($user, $room));
    }

    public function test_a_regular_member_cannot_manage_rooms()
    {
        $user = factory(User::class)->create();
        $room = factory(Room::class)->create();

        $this->assertFalse($this->policy->create($user));
        $this->assertFalse($this->policy->update($user, $room));
        $this->assertFalse($this->policy->delete($user, $room));
    }

    public function test_equipment_managers_can_manage_rooms()
    {
        $equipmentUser = factory(User::class)->create();
        $equipmentUser->assignRole(
            Role::firstOrCreate(['name' => 'equipment'], ['title' => 'Equipment'])
        );

        $maintainer = factory(User::class)->create();
        $maintainer->maintainerGroups()->attach(factory(MaintainerGroup::class)->create());

        $room = factory(Room::class)->create();

        $this->assertTrue($this->policy->create($equipmentUser));
        $this->assertTrue($this->policy->update($maintainer, $room));
    }
}
