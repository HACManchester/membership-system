<?php

namespace Tests\Feature;

use BB\Entities\Role;
use BB\Entities\Settings;
use BB\Entities\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SettingsPolicyTest extends TestCase
{
    #[Test]
    public function an_admin_can_view_and_update_settings()
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->can('viewAny', Settings::class));
        $this->assertTrue($admin->can('update', Settings::class));
    }

    #[DataProvider('nonAdminRoles')]
    #[Test]
    public function a_member_without_the_admin_role_cannot_view_or_update_settings(?string $roleName)
    {
        $member = User::factory()->create();
        if ($roleName !== null) {
            $member->assignRole(Role::firstOrCreate(['name' => $roleName], ['title' => $roleName]));
        }

        $this->assertFalse($member->can('viewAny', Settings::class));
        $this->assertFalse($member->can('update', Settings::class));
    }

    public static function nonAdminRoles(): array
    {
        return [
            'no role' => [null],
            'board' => ['board'],
            'finance' => ['finance'],
            'equipment' => ['equipment'],
        ];
    }
}
