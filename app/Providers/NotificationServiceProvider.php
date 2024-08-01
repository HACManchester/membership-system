<?php

namespace BB\Providers;

use BB\FlashNotification\FlashNotificationManager;
use Illuminate\Support\ServiceProvider;

/**
 * Inline and simplify 'arthurguy/notifications' functionality for Laravel 6+ support
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
        $this->app->singleton('flash_notification_manager', function () {
            return $this->app->make(FlashNotificationManager::class);
        });
    }
}
