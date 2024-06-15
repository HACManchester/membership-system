<?php

namespace BB\Listeners;

use BB\Jobs\DiscourseSync;
use Illuminate\Auth\Events\Login as Login;

class DiscourseSyncSubscriber
{
    public function subscribe($events)
    {
        $events->listen(
            Login::class,
            [DiscourseSyncSubscriber::class, 'handleLogin']
        );
    }

    public static function handleLogin(Login $event)
    {
        DiscourseSync::dispatch($event->user);
    }
}
