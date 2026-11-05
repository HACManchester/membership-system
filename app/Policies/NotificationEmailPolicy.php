<?php

namespace BB\Policies;

use BB\Entities\Equipment;
use BB\Entities\User;
use BB\Repo\TrainingRecordRepository;

/**
 * Who may email members. There's no model behind an email, so these are
 * registered as gate abilities in AuthServiceProvider.
 */
class NotificationEmailPolicy
{
    public function __construct(private TrainingRecordRepository $trainingRecordRepository)
    {
    }

    /**
     * Admins and anyone holding a role can open the mass email form.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->roles()->exists();
    }

    /**
     * Admins and the tool's active trainers can email its members by training
     * status. Unlike EquipmentPolicy, the equipment role alone isn't enough.
     */
    public function emailEquipmentMembers(User $user, Equipment $equipment): bool
    {
        return $user->isAdmin()
            || $this->trainingRecordRepository->getTrainersForEquipment($equipment)
                ->contains(fn ($trainer) => $trainer->user->is($user));
    }
}
