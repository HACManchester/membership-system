<?php

namespace Tests\Feature;

use BB\Entities\AccessLockdown;
use BB\Entities\Role;
use BB\Entities\User;
use Tests\TestCase;
use Illuminate\Support\Str;

class KeyFobCsvTest extends TestCase
{
    public function testKeyFobsCsvList()
    {
        $token = \BB\Entities\ApiKey::create(['api_token' => Str::random(), 'description' => 'Test Key', 'active' => true]);

        $user_1 = factory('BB\Entities\User')->create(['active' => true, 'announce_name' => 'User One']);
        $user_2 = factory('BB\Entities\User')->create(['active' => true, 'announce_name' => null]);
        $user_3 = factory('BB\Entities\User')->create(['active' => false, 'announce_name' => 'Inactive User']);
        $user_4 = factory('BB\Entities\User')->create(['active' => true, 'announce_name' => 'Inactive Fob']);
        $user_5 = factory('BB\Entities\User')->create(['active' => true, 'announce_name' => 'No fob']);

        $keyfob_1 = factory('BB\Entities\KeyFob')->create(['key_id' => 'keyfob_1', 'active' => true, 'user_id' => $user_1->id]);
        $keyfob_2 = factory('BB\Entities\KeyFob')->create(['key_id' => 'keyfob_2', 'active' => true, 'user_id' => $user_2->id]);
        $keyfob_3 = factory('BB\Entities\KeyFob')->create(['key_id' => 'keyfob_3', 'active' => true, 'user_id' => $user_3->id]);
        $keyfob_4 = factory('BB\Entities\KeyFob')->create(['key_id' => 'keyfob_4', 'active' => false, 'user_id' => $user_4->id]);

        $response = $this->get('/api/keyfobs/csv?api_token=' . $token->api_token);

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $expectedContent = <<<'CSV'
key_id,announce_name,id
keyfob_1,"User One",1
keyfob_2,,2

CSV;
        $this->assertEquals($expectedContent, $content);
    }

    /** @test */
    public function a_lockdown_restricts_the_export_to_its_roles()
    {
        $this->createCommitteeRole();

        $this->memberWithFob('Admin', 'fob_admin', 'admin');
        $this->memberWithFob('Committee', 'fob_committee', 'committee');
        $this->memberWithFob('Board', 'fob_board', 'board');
        $this->memberWithFob('Ordinary', 'fob_ordinary');

        $this->startLockdown(['admin', 'committee', 'board']);

        $rows = $this->exportRows();

        $this->assertEqualsCanonicalizing(
            ['fob_admin', 'fob_committee', 'fob_board'],
            $rows
        );
    }

    /** @test */
    public function a_lockdown_uses_its_own_role_list_not_the_config_default()
    {
        $this->memberWithFob('Safety', 'fob_safety', 'safety');
        $this->memberWithFob('Board', 'fob_board', 'board');
        $this->memberWithFob('Admin', 'fob_admin', 'admin');

        $this->startLockdown(['admin', 'safety']);

        $rows = $this->exportRows();

        $this->assertEqualsCanonicalizing(['fob_admin', 'fob_safety'], $rows);
    }

    /** @test */
    public function a_lockdown_never_widens_the_export()
    {
        $inactive = $this->memberWithFob('Inactive Admin', 'fob_inactive', 'admin');
        $inactive->update(['active' => false]);

        $banned = $this->memberWithFob('Banned Admin', 'fob_banned', 'admin');
        $banned->update(['banned' => true]);

        $this->memberWithFob('Active Admin', 'fob_active', 'admin');

        $this->startLockdown(['admin']);

        $this->assertEquals(['fob_active'], $this->exportRows());
    }

    /** @test */
    public function lifting_a_lockdown_restores_the_full_list()
    {
        $admin = $this->memberWithFob('Admin', 'fob_admin', 'admin');
        $this->memberWithFob('Ordinary', 'fob_ordinary');

        $lockdown = $this->startLockdown(['admin']);
        $this->assertEquals(['fob_admin'], $this->exportRows());

        $lockdown->update(['lifted_at' => now(), 'lifted_by' => $admin->id]);

        $this->assertEqualsCanonicalizing(
            ['fob_admin', 'fob_ordinary'],
            $this->exportRows()
        );
    }

    private function createCommitteeRole(): Role
    {
        // `committee` exists in production but isn't created by any migration.
        return Role::create(['name' => 'committee', 'title' => 'Committee']);
    }

    private function memberWithFob(string $announceName, string $keyId, ?string $role = null): User
    {
        $user = factory(User::class)->create([
            'active' => true,
            'announce_name' => $announceName,
        ]);

        if ($role) {
            $user->assignRole(Role::findByName($role));
        }

        factory('BB\Entities\KeyFob')->create([
            'key_id' => $keyId,
            'active' => true,
            'user_id' => $user->id,
        ]);

        return $user;
    }

    /**
     * @param  string[]  $roles
     */
    private function startLockdown(array $roles): AccessLockdown
    {
        return AccessLockdown::create([
            'started_by' => factory(User::class)->create()->id,
            'reason' => 'Testing',
            'roles' => $roles,
        ]);
    }

    private function export()
    {
        $token = \BB\Entities\ApiKey::create([
            'api_token' => Str::random(),
            'description' => 'Test Key',
        ]);

        return $this->get('/api/keyfobs/csv?api_token=' . $token->api_token);
    }

    /**
     * The `key_id` column of every exported row, header excluded.
     *
     * @return string[]
     */
    private function exportRows(): array
    {
        $response = $this->export();
        $response->assertStatus(200);

        $lines = array_filter(explode("\n", $response->streamedContent()));
        array_shift($lines);

        return array_map(function ($line) {
            return explode(',', $line)[0];
        }, array_values($lines));
    }
}
