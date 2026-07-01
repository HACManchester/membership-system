<?php

use BB\Entities\User;
use BB\Process\CheckSuspendedUsers;
use BB\Repo\UserRepository;
use Carbon\Carbon;
use Tests\TestCase;

class CheckSuspendedUsersTest extends TestCase
{
    private $process;

    public function setUp(): void
    {
        parent::setUp();
        $this->process = new CheckSuspendedUsers(app(UserRepository::class));
    }

    public function testMarksUserAsLeftAfterThirtyDaysSuspended()
    {
        $user = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'suspended_at' => Carbon::now()->subDays(31),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('left', $user->status);
    }

    public function testLeavesRecentlySuspendedUserAlone()
    {
        $user = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'suspended_at' => Carbon::now()->subDays(10),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('suspended', $user->status);
    }

    public function testBackfillsMissingSuspensionDateSoTheClockStarts()
    {
        $user = factory(User::class)->create([
            'status' => 'suspended',
            'active' => false,
            'suspended_at' => null,
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('suspended', $user->status);
        $this->assertNotNull($user->suspended_at);
        $this->assertTrue($user->suspended_at->isToday());
    }
}
