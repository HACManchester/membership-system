<?php

namespace BB\Providers;

// use Illuminate\Support\Facades\Gate;

use BB\Entities\Equipment;
use BB\Entities\KeyFob;
use BB\Policies\EquipmentPolicy;
use BB\Policies\KeyFobPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Equipment::class => EquipmentPolicy::class,
        KeyFob::class => KeyFobPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}