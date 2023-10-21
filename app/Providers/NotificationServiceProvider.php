<?php

namespace BB\Providers;

use BB\Entities\Activity;
use BB\Entities\User;
use BB\Observer\ActivityObserver;
use BB\Observer\UserAuditObserver;
use BB\Observer\UserObserver;
use Illuminate\Support\ServiceProvider;

/**
 * Adapted version of ArthurGuy\Notifications\NotificationServiceProvider
 *
 * Replaces 'bindShared' with 'singleton' for compatibility with newer Laravel versions
 */
class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'ArthurGuy\Notifications\SessionStore',
            'ArthurGuy\Notifications\LaravelSessionStore'
        );

        $this->app->singleton('notification', function () {
            return $this->app->make('ArthurGuy\Notifications\Notifier');
        });
    }
}
