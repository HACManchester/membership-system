<?php

use BB\Entities\User;
use BB\Events\MemberBecameActive;
use BB\Events\MemberBecameInactive;
use BB\Events\MemberDiscourseParamsChanged;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserObserverTest extends TestCase
{
    public function testBroadcastsMemberBecameActive()
    {
        Event::fake([MemberBecameActive::class]);

        $user = factory(User::class)->create([
            'active' => false,
            'status' => 'setting-up',
        ]);

        $user->update([
            'active' => true,
            'status' => 'active',
        ]);

        Event::assertDispatched(MemberBecameActive::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    public function testBroadcastsMemberBecameInactive()
    {
        Event::fake([MemberBecameInactive::class]);

        $user = factory(User::class)->create([
            'active' => true,
            'status' => 'active',
        ]);

        $user->update([
            'active' => false,
            'status' => 'left',
        ]);

        Event::assertDispatched(MemberBecameInactive::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    public function testBroadcastsMemberDiscourseParamsChanged()
    {
        Event::fake([MemberDiscourseParamsChanged::class]);

        $user = factory(User::class)->create([
            'given_name' => 'John',
        ]);

        $user->update([
            'given_name' => 'Johnny',
        ]);

        Event::assertDispatched(MemberDiscourseParamsChanged::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }
}
