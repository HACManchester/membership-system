<?php

namespace BB\Policies;

use BB\Entities\User;
use BB\Entities\Equipment;
use BB\Repo\InductionRepository;
use Illuminate\Auth\Access\HandlesAuthorization;

class EquipmentPolicy
{
    use HandlesAuthorization;

    protected $inductionRepository;

    public function __construct(InductionRepository $inductionRepository)
    {
        $this->inductionRepository = $inductionRepository;
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

    /**
     * Determine whether the user can update the equipment.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\Equipment  $equipment
     * @return mixed
     */
    public function update(User $user, Equipment $equipment)
    {
        $inMaintainerGroup = $user->maintainerGroups->contains($equipment->maintainerGroup);
        $isAreaCoordinator = $user->equipmentAreas->contains($equipment->maintainerGroup->equipmentArea);
        $inManagingRole = $equipment->role ? $user->hasRole($equipment->role->name) : false;

        return $inMaintainerGroup || $isAreaCoordinator || $inManagingRole;
    }

    /**
     * Determine whether the user can delete the equipment.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\Equipment  $equipment
     * @return mixed
     */
    public function delete(User $user, Equipment $equipment)
    {
        $inMaintainerGroup = $user->maintainerGroups->contains($equipment->maintainerGroup);
        $isAreaCoordinator = $user->equipmentAreas->contains($equipment->maintainerGroup->equipmentArea);
        $inManagingRole = $equipment->role ? $user->hasRole($equipment->role->name) : false;

        return $inMaintainerGroup || $isAreaCoordinator || $inManagingRole;
    }

    public function train(User $user, Equipment $equipment)
    {
        return $this->inductionRepository->isTrainerForEquipment($user, $equipment->induction_category);
    }
}
