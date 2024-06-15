<?php

use BB\Entities\User;
use BB\Events\MemberBecameInactive;
use BB\Events\MemberBecameActive;
use BB\Jobs\DiscourseSync;
use BB\Listeners\DiscourseSyncSubscriber;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class DiscourseSyncSubscriberTest extends TestCase
{
    public function testsLogin()
    {
        Bus::fake();

        $user = factory(User::class)->create([
            'display_name' => 'Listening Larry'
        ]);

        Event::dispatch(
            (new Login('test-guard', $user, false))
        );

        Bus::assertDispatched(DiscourseSync::class, function ($job) {
            return $job->user->display_name === 'Listening Larry';
        });
    }

    public function testsMemberBecameActive()
    {
        Bus::fake();

        $user = factory(User::class)->create([
            'display_name' => 'Active Alan'
        ]);

        Event::dispatch(
            (new MemberBecameActive($user))
        );

        Bus::assertDispatched(DiscourseSync::class, function ($job) {
            return $job->user->display_name === 'Active Alan';
        });
    }

    public function testsMemberBecameInactive()
    {
        Bus::fake();

        $user = factory(User::class)->create([
            'display_name' => 'Inactive Ivan'
        ]);

        Event::dispatch(
            (new MemberBecameInactive($user))
        );

        Bus::assertDispatched(DiscourseSync::class, function ($job) {
            return $job->user->display_name === 'Inactive Ivan';
        });
    }
}
