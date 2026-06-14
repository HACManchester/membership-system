<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Assert;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DisciplinaryTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
    public function testBan()
    {
        $now = Carbon::now()->startOfMinute();
        Carbon::setTestNow($now);

        $admin = factory('BB\Entities\User')->states('admin')->create();
        $user = factory('BB\Entities\User')->create();

        Assert::assertArraySubset(
            [
                'active' => true,
                'status' => 'active',
                'banned' => false,
                'banned_reason' => null,
                'banned_date' => null
            ],
            $user->fresh()->toArray()
        );

        $this
            ->actingAs($admin)
            ->post(
                route('disciplinary.ban', $user),
                ['reason' => 'Testing']
            );

        Assert::assertArraySubset(
            [
                'active' => false,
                'status' => 'left',
                'banned' => true,
                'banned_reason' => 'Testing',
                'banned_date' => $now->toIso8601ZuluString('microsecond')
            ],
            $user->fresh()->toArray()
        );
    }

    public function testUnban()
    {
        $now = Carbon::now()->startOfMinute();
        Carbon::setTestNow($now);

        $admin = factory('BB\Entities\User')->states('admin')->create();
        $user = factory('BB\Entities\User')->create([
            'active' => false,
            'status' => 'left',
            'banned' => true,
            'banned_reason' => 'Testing',
            'banned_date' => $now,
        ]);

        Assert::assertArraySubset(
            [
                'active' => false,
                'status' => 'left',
                'banned' => true,
                'banned_reason' => 'Testing',
                'banned_date' => $now->toIso8601ZuluString('microsecond')
            ],
            $user->fresh()->toArray()
        );

        $this
            ->actingAs($admin)
            ->post(route('disciplinary.unban', $user));

        Assert::assertArraySubset(
            [
                'banned' => false,
                'banned_reason' => null,
                'banned_date' => null
            ],
            $user->fresh()->toArray()
        );
    }

    /** @test */
    public function a_non_admin_cannot_ban_a_member()
    {
        $member = factory('BB\Entities\User')->create();
        $target = factory('BB\Entities\User')->create();

        $this->actingAs($member)
            ->post(route('disciplinary.ban', $target), ['reason' => 'because'])
            ->assertStatus(403);

        $this->assertFalse((bool) $target->fresh()->banned);
    }

    /** @test */
    public function an_admin_cannot_ban_themselves()
    {
        $admin = factory('BB\Entities\User')->states('admin')->create();

        $this->actingAs($admin)
            ->post(route('disciplinary.ban', $admin), ['reason' => 'oops'])
            ->assertStatus(403);

        $this->assertFalse((bool) $admin->fresh()->banned);
    }

    /** @test */
    public function a_non_admin_cannot_unban_a_member()
    {
        $member = factory('BB\Entities\User')->create();
        $banned = factory('BB\Entities\User')->create([
            'active' => false,
            'status' => 'left',
            'banned' => true,
        ]);

        $this->actingAs($member)
            ->post(route('disciplinary.unban', $banned))
            ->assertStatus(403);

        $this->assertTrue((bool) $banned->fresh()->banned);
    }
}
