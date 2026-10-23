<?php

namespace BB\Providers;

use BB\Entities\ProfileData;
use BB\Entities\User;
use BB\Observer\UserAuditObserver;
use BB\Observer\UserObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(new UserObserver());
        User::observe(new UserAuditObserver());
        // Registered here rather than in ProfileData::boot(): since Laravel 13,
        // observe() instantiates the model, which isn't allowed while it boots
        ProfileData::observe(new UserAuditObserver());
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
