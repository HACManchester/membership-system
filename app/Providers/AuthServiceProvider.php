<?php

namespace BB\Providers;

// use Illuminate\Support\Facades\Gate;

use BB\Entities\Course;
use BB\Entities\Equipment;
use BB\Entities\EquipmentArea;
use BB\Entities\Induction;
use BB\Entities\KeyFob;
use BB\Entities\StorageBox;
use BB\Entities\User;
use BB\Entities\MaintainerGroup;
use BB\Policies\EquipmentAreaPolicy;
use BB\Policies\EquipmentPolicy;
use BB\Policies\InductionPolicy;
use BB\Policies\KeyFobPolicy;
use BB\Policies\MaintainerGroupPolicy;
use BB\Policies\CoursePolicy;
use BB\Policies\StorageBoxPolicy;
use BB\Policies\UserPolicy;
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
        User::class => UserPolicy::class,
        StorageBox::class => StorageBoxPolicy::class,
        EquipmentArea::class => EquipmentAreaPolicy::class,
        Induction::class => InductionPolicy::class,
        MaintainerGroup::class => MaintainerGroupPolicy::class,
        Course::class => CoursePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}