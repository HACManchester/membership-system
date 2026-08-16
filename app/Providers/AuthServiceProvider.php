<?php

namespace BB\Providers;

// use Illuminate\Support\Facades\Gate;

use BB\Entities\AccessLockdown;
use BB\Entities\Course;
use BB\Entities\Equipment;
use BB\Entities\EquipmentArea;
use BB\Entities\TrainingRecord;
use BB\Entities\KeyFob;
use BB\Entities\Room;
use BB\Entities\User;
use BB\Entities\MaintainerGroup;
use BB\Policies\AccessLockdownPolicy;
use BB\Policies\EquipmentAreaPolicy;
use BB\Policies\EquipmentPolicy;
use BB\Policies\RoomPolicy;
use BB\Policies\TrainingRecordPolicy;
use BB\Policies\KeyFobPolicy;
use BB\Policies\MaintainerGroupPolicy;
use BB\Policies\CoursePolicy;
use BB\Policies\RolePolicy;
use BB\Policies\UserPolicy;
use BB\Entities\Role;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        EquipmentArea::class => EquipmentAreaPolicy::class,
        TrainingRecord::class => TrainingRecordPolicy::class,
        MaintainerGroup::class => MaintainerGroupPolicy::class,
        Course::class => CoursePolicy::class,
        Room::class => RoomPolicy::class,
        Role::class => RolePolicy::class,
        AccessLockdown::class => AccessLockdownPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // The member-search endpoint backs the training pickers, so restrict it to
        // the people who manage inductions rather than every logged-in member.
        Gate::define('search-members', function (User $user) {
            return $user->isAdmin()
                || $user->hasRole('equipment')
                || $user->maintainerGroups()->exists()
                || $user->equipmentAreas()->exists()
                || TrainingRecord::where('user_id', $user->id)->where('is_trainer', true)->exists();
        });
    }
}