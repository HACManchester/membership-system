<?php

namespace BB\Policies;

use BB\Entities\Equipment;
use BB\Entities\User;
use BB\Entities\TrainingRecord;
use BB\Repo\TrainingRecordRepository;
use Illuminate\Auth\Access\HandlesAuthorization;

class TrainingRecordPolicy
{
    use HandlesAuthorization;

    protected $trainingRecordRepository;

    public function __construct(TrainingRecordRepository $trainingRecordRepository)
    {
        $this->trainingRecordRepository = $trainingRecordRepository;
    }

    public function before($user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }

        // fall through to policy methods
        return null;
    }

    public function view(User $user, TrainingRecord $trainingRecord)
    {
        return $this->trainingRecordRepository->isTrainerForEquipment($user, $trainingRecord->key);
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
        return $this->trainingRecordRepository->isTrainerForEquipment($user, $equipment->induction_category);
    }

    public function train(User $user, TrainingRecord $trainingRecord)
    {
        // Use course-based authorization if course_id is set, otherwise fall back to equipment-based
        if ($trainingRecord->course_id) {
            return $this->trainingRecordRepository->isTrainerForCourse($user, $trainingRecord->course_id);
        }
        
        return $this->trainingRecordRepository->isTrainerForEquipment($user, $trainingRecord->key);
    }

    public function untrain(User $user, TrainingRecord $trainingRecord)
    {
        // Use course-based authorization if course_id is set, otherwise fall back to equipment-based
        if ($trainingRecord->course_id) {
            return $this->trainingRecordRepository->isTrainerForCourse($user, $trainingRecord->course_id);
        }
        
        return $this->trainingRecordRepository->isTrainerForEquipment($user, $trainingRecord->key);
    }

    public function promote(User $user, TrainingRecord $trainingRecord)
    {
        // Use course-based authorization if course_id is set, otherwise fall back to equipment-based
        if ($trainingRecord->course_id) {
            return $this->trainingRecordRepository->isTrainerForCourse($user, $trainingRecord->course_id);
        }
        
        return $this->trainingRecordRepository->isTrainerForEquipment($user, $trainingRecord->key);
    }

    public function demote(User $user, TrainingRecord $trainingRecord)
    {
        // Use course-based authorization if course_id is set, otherwise fall back to equipment-based
        if ($trainingRecord->course_id) {
            return $this->trainingRecordRepository->isTrainerForCourse($user, $trainingRecord->course_id);
        }
        
        return $this->trainingRecordRepository->isTrainerForEquipment($user, $trainingRecord->key);
    }

    public function delete(User $user, TrainingRecord $trainingRecord)
    {
        return $this->trainingRecordRepository->isTrainerForEquipment($user, $trainingRecord->key);
    }
}
