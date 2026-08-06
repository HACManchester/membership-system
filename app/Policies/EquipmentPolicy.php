<?php

namespace BB\Policies;

use BB\Entities\User;
use BB\Entities\Equipment;
use BB\Repo\TrainingRecordRepository;
use Illuminate\Auth\Access\HandlesAuthorization;

class EquipmentPolicy
{
    use HandlesAuthorization;

    protected $trainingRecordRepository;

    public function __construct(TrainingRecordRepository $trainingRecordRepository)
    {
        $this->trainingRecordRepository = $trainingRecordRepository;
    }

    public function before($user, $ability)
    {
        if ($user->isAdmin() || $user->hasRole('equipment')) {
            return true;
        }

        // fall through to policy methods
        return null;
    }

    /**
     * Determine whether the user can view the equipment.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\Equipment  $equipment
     * @return mixed
     */
    public function view(User $user, Equipment $equipment)
    {
        return true;
    }

    /**
     * Determine whether the user can create equipment.
     *
     * @param  \BB\Entities\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        // If they're in a maintainer group, they can create equipment managed by their group
        $isMaintainer =  $user->maintainerGroups()->count() > 0;
        $isAreaCoordinator = $user->equipmentAreas()->count() > 0;
        return $isMaintainer || $isAreaCoordinator;
    }

    public function update(User $user, Equipment $equipment)
    {
        return $this->manages($user, $equipment);
    }

    public function delete(User $user, Equipment $equipment)
    {
        return $this->manages($user, $equipment);
    }

    /**
     * Whether the user manages this equipment through its maintainer group, that
     * group's area, or a managing role. Group-less equipment has no such managers,
     * so it's editable only by the admins / equipment role handled in before().
     */
    private function manages(User $user, Equipment $equipment): bool
    {
        $group = $equipment->maintainerGroup;

        $inMaintainerGroup = $group && $user->maintainerGroups->contains($group);
        $isAreaCoordinator = $group && $group->equipmentArea
            && $user->equipmentAreas->contains($group->equipmentArea);
        $inManagingRole = $equipment->role ? $user->hasRole($equipment->role->name) : false;

        return $inMaintainerGroup || $isAreaCoordinator || $inManagingRole;
    }

    public function train(User $user, Equipment $equipment)
    {
        return $this->trainingRecordRepository->isTrainerForEquipment($user, $equipment);
    }
}
