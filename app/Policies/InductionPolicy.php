<?php

namespace BB\Policies;

use BB\Entities\Equipment;
use BB\Entities\User;
use BB\Entities\induction;
use BB\Repo\InductionRepository;
use Illuminate\Auth\Access\HandlesAuthorization;

class InductionPolicy
{
    use HandlesAuthorization;

    protected $inductionRepository;

    public function __construct(InductionRepository $inductionRepository)
    {
        $this->inductionRepository = $inductionRepository;
    }

    public function before($user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }

        // fall through to policy methods
        return null;
    }

    /**
     * Determine whether the user can view the induction.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\Induction  $induction
     * @return mixed
     */
    public function view(User $user, Induction $induction)
    {
        return $this->inductionRepository->isTrainerForEquipment($user, $induction->key);
    }

    /**
     * Determine whether the user can create induction.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\Equipment  $Equipment
     * @param  \BB\Entities\User|null  $userAwaitingTraining
     * @return mixed
     */
    public function create(User $user, Equipment $equipment, $userAwaitingTraining = null)
    {
        // Requesting for self
        if ($userAwaitingTraining === null) {
            return true;
        }

        // Requesting for others
        return $this->inductionRepository->isTrainerForEquipment($user, $equipment->induction_category);
    }

    /**
     * Determine whether the user can mark the inductee as trained.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\induction  $induction
     * @return mixed
     */
    public function train(User $user, Induction $induction)
    {
        // Use course-based authorization if course_id is set, otherwise fall back to equipment-based
        if ($induction->course_id) {
            return $this->inductionRepository->isTrainerForCourse($user, $induction->course_id);
        }
        
        return $this->inductionRepository->isTrainerForEquipment($user, $induction->key);
    }

    /**
     * Determine whether the user can remove the trained flag from the inductee
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\induction  $induction
     * @return mixed
     */
    public function untrain(User $user, Induction $induction)
    {
        // Use course-based authorization if course_id is set, otherwise fall back to equipment-based
        if ($induction->course_id) {
            return $this->inductionRepository->isTrainerForCourse($user, $induction->course_id);
        }
        
        return $this->inductionRepository->isTrainerForEquipment($user, $induction->key);
    }

    /**
     * Determine whether the user can promote the inductee as a trainer
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\induction  $induction
     * @return mixed
     */
    public function promote(User $user, Induction $induction)
    {
        // Use course-based authorization if course_id is set, otherwise fall back to equipment-based
        if ($induction->course_id) {
            return $this->inductionRepository->isTrainerForCourse($user, $induction->course_id);
        }
        
        return $this->inductionRepository->isTrainerForEquipment($user, $induction->key);
    }

    /**
     * Determine whether the user can demote the inductee from being a trainer
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\induction  $induction
     * @return mixed
     */
    public function demote(User $user, Induction $induction)
    {
        // Use course-based authorization if course_id is set, otherwise fall back to equipment-based
        if ($induction->course_id) {
            return $this->inductionRepository->isTrainerForCourse($user, $induction->course_id);
        }
        
        return $this->inductionRepository->isTrainerForEquipment($user, $induction->key);
    }

    /**
     * Determine whether the user can delete the induction.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\induction  $induction
     * @return mixed
     */
    public function delete(User $user, Induction $induction)
    {
        return $this->inductionRepository->isTrainerForEquipment($user, $induction->key);
    }
}
