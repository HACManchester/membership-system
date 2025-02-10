<?php

namespace BB\Policies;

use BB\Entities\User;
use BB\Entities\EquipmentArea;
use Illuminate\Auth\Access\HandlesAuthorization;

class EquipmentAreaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any equipment areas.
     *
     * @param  \BB\Entities\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the equipment area.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\EquipmentArea  $equipmentArea
     * @return mixed
     */
    public function view(User $user, EquipmentArea $equipmentArea)
    {
        return true;
    }

    /**
     * Determine whether the user can create equipment areas.
     *
     * @param  \BB\Entities\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the equipment area.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\EquipmentArea  $equipmentArea
     * @return mixed
     */
    public function update(User $user, EquipmentArea $equipmentArea)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the equipment area.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\EquipmentArea  $equipmentArea
     * @return mixed
     */
    public function delete(User $user, EquipmentArea $equipmentArea)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the equipment area.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\EquipmentArea  $equipmentArea
     * @return mixed
     */
    public function restore(User $user, EquipmentArea $equipmentArea)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the equipment area.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\EquipmentArea  $equipmentArea
     * @return mixed
     */
    public function forceDelete(User $user, EquipmentArea $equipmentArea)
    {
        return $user->isAdmin();
    }
}
