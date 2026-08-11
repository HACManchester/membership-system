<?php

use BB\Entities\SubscriptionCharge;
use BB\Entities\User;
use BB\Process\CheckLeavingUsers;
use BB\Repo\UserRepository;
use Carbon\Carbon;
use Tests\TestCase;

class CheckLeavingUsersTest extends TestCase
{
    private $process;

    public function setUp(): void
    {
        parent::setUp();
        $this->process = new CheckLeavingUsers(app(UserRepository::class));
    }

    public function testMarksLeavingUserAsLeftOnceTheirMembershipExpires()
    {
        $user = factory(User::class)->create([
            'status' => 'leaving',
            'active' => true,
            'subscription_expires' => Carbon::now()->subDay(),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('left', $user->status);
        $this->assertFalse((bool) $user->active);
    }

    public function testLeavesAPaidUpLeavingUserAlone()
    {
        $user = factory(User::class)->create([
            'status' => 'leaving',
            'active' => true,
            'subscription_expires' => Carbon::now()->addDays(10),
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('leaving', $user->status);
        $this->assertTrue((bool) $user->active);
    }

    public function testMarksLeavingUserWithNoExpiryDateAsLeft()
    {
        // These used to sit here forever: the old code logged an error and moved on, so
        // the member stayed active and the nightly run raised a charge every month that
        // nothing could collect
        $user = factory(User::class)->create([
            'status' => 'leaving',
            'active' => true,
            'subscription_expires' => null,
        ]);

        $this->process->run();

        $user->refresh();
        $this->assertEquals('left', $user->status);
        $this->assertFalse((bool) $user->active);
    }

    public function testCancelsOutstandingChargesWhenTheMemberLeaves()
    {
        $user = factory(User::class)->create([
            'status' => 'leaving',
            'active' => true,
            'subscription_expires' => null,
        ]);

        $charge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'status' => 'due',
        ]);

        $this->process->run();

        $this->assertEquals('cancelled', $charge->fresh()->status);
    }
}
