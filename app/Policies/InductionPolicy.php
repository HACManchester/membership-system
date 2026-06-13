<?php

namespace BB\Policies;

use BB\Entities\Equipment;
use BB\Entities\User;
use BB\Entities\Induction;
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

    public function view(User $user, Induction $induction)
    {
        return $this->inductionRepository->isTrainerForEquipment($user, $induction->key);
    }

    /**
     * Determine whether the user can create induction.
     *
     * @param  \BB\Entities\User|null  $userAwaitingTraining
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

    public function train(User $user, Induction $induction)
    {
        // Use course-based authorization if course_id is set, otherwise fall back to equipment-based
        if ($induction->course_id) {
            return $this->inductionRepository->isTrainerForCourse($user, $induction->course_id);
        }
        
        return $this->inductionRepository->isTrainerForEquipment($user, $induction->key);
    }

    public function untrain(User $user, Induction $induction)
    {
        // Use course-based authorization if course_id is set, otherwise fall back to equipment-based
        if ($induction->course_id) {
            return $this->inductionRepository->isTrainerForCourse($user, $induction->course_id);
        }
        
        return $this->inductionRepository->isTrainerForEquipment($user, $induction->key);
    }

    public function promote(User $user, Induction $induction)
    {
        // Use course-based authorization if course_id is set, otherwise fall back to equipment-based
        if ($induction->course_id) {
            return $this->inductionRepository->isTrainerForCourse($user, $induction->course_id);
        }
        
        return $this->inductionRepository->isTrainerForEquipment($user, $induction->key);
    }

    public function demote(User $user, Induction $induction)
    {
        // Use course-based authorization if course_id is set, otherwise fall back to equipment-based
        if ($induction->course_id) {
            return $this->inductionRepository->isTrainerForCourse($user, $induction->course_id);
        }
        
        return $this->inductionRepository->isTrainerForEquipment($user, $induction->key);
    }

    public function delete(User $user, Induction $induction)
    {
        return $this->inductionRepository->isTrainerForEquipment($user, $induction->key);
    }
}
