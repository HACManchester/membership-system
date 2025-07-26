<?php

namespace BB\Policies;

use BB\Entities\User;
use BB\Entities\Course;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }

        // fall through to policy methods
        return null;
    }

    /**
     * Determine whether the user can view any induction courses.
     *
     * @param  \BB\Entities\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        // Check if inductions feature is live for everyone
        if (Course::isLive()) {
            return true;
        }

        // Area coordinators can access
        if ($user->equipmentAreas()->count() > 0) {
            return true;
        }

        // Equipment maintainers can access
        if ($user->maintainerGroups()->count() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the induction course.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\Course  $course
     * @return mixed
     */
    public function view(User $user, Course $course)
    {
        return true;
    }

    /**
     * Determine whether the user can create induction courses.
     *
     * @param  \BB\Entities\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        // Area coordinators can make induction courses
        return $user->equipmentAreas()->count() > 0;
    }

    /**
     * Determine whether the user can update the induction course.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\Course  $course
     * @return mixed
     */
    public function update(User $user, Course $course)
    {
        return $this->isMaintainerOrCoordinator($user, $course);
    }

    /**
     * Determine whether the user can delete the induction course.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\Course  $course
     * @return mixed
     */
    public function delete(User $user, Course $course)
    {
        return $this->isMaintainerOrCoordinator($user, $course);
    }

    /**
     * Determine whether the user can restore the induction course.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\Course  $course
     * @return mixed
     */
    public function restore(User $user, Course $course)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the induction course.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\Course  $course
     * @return mixed
     */
    public function forceDelete(User $user, Course $course)
    {
        return false;
    }

    public function viewTraining(User $user, Course $course)
    {
        $inductionRepo = app(\BB\Repo\InductionRepository::class);
        return $inductionRepo->isTrainerForCourse($user, $course->id);
    }

    public function train(User $user, Course $course)
    {
        $inductionRepo = app(\BB\Repo\InductionRepository::class);
        return $inductionRepo->isTrainerForCourse($user, $course->id);
    }

    public function requestSignOff(User $user, Course $course)
    {
        // Users can request sign-off for themselves if course is not paused
        return !$course->isPaused();
    }


    protected function isMaintainerOrCoordinator(User $user, Course $course)
    {
        $maintainerGroups = $course->equipment->map(function ($equipment) {
            return $equipment->maintainerGroup;
        });

        $maintainers = $maintainerGroups->flatMap(function ($maintainerGroup) {
            return $maintainerGroup->maintainers;
        });

        $areaCoordinators = $maintainerGroups->flatMap(function ($maintainerGroup) {
            return $maintainerGroup->equipmentArea->areaCoordinators;
        });

        $authorizedUsers = $maintainers->merge($areaCoordinators);
        
        return $authorizedUsers->contains('id', $user->id);
    }
}
