<?php

namespace BB\Listeners;

use BB\Events\MemberBecameActive;
use BB\Events\MemberBecameInactive;
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
        $events->listen(
            MemberBecameActive::class,
            [DiscourseSyncSubscriber::class, 'handleMembershipBecameActive']
        );
        $events->listen(
            MemberBecameInactive::class,
            [DiscourseSyncSubscriber::class, 'handleMembershipBecameInactive']
        );
    }

    public static function handleLogin(Login $event)
    {
        DiscourseSync::dispatch($event->user);
    }

    public static function handleMembershipBecameActive(MemberBecameActive $event)
    {
        DiscourseSync::dispatch($event->user);
    }

    public static function handleMembershipBecameInactive(MemberBecameInactive $event)
    {
        DiscourseSync::dispatch($event->user);
    }
}
