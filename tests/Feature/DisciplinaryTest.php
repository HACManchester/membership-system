<?php

namespace Tests\Feature;

use Illuminate\Testing\Assert;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DisciplinaryTest extends TestCase
{
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
}
