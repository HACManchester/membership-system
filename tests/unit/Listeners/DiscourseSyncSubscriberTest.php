<?php

use BB\Entities\User;
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
}
