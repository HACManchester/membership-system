<?php

namespace BB\Policies;

use BB\Entities\User;
use BB\Entities\MaintainerGroup;
use Illuminate\Auth\Access\HandlesAuthorization;

class MaintainerGroupPolicy
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
     * Determine whether the user can view any maintainer groups.
     *
     * @param  \BB\Entities\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the maintainer group.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\MaintainerGroup  $maintainerGroup
     * @return mixed
     */
    public function view(User $user, MaintainerGroup $maintainerGroup)
    {
        return true;
    }

    /**
     * Determine whether the user can create maintainer groups.
     *
     * @param  \BB\Entities\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        // Area coordinators can make maintainer groups
        return $user->equipmentAreas()->count() > 0;
    }

    /**
     * Determine whether the user can update the maintainer group.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\MaintainerGroup  $maintainerGroup
     * @return mixed
     */
    public function update(User $user, MaintainerGroup $maintainerGroup)
    {
        $isMaintainer = $maintainerGroup->maintainers->contains($user);
        $isAreaCoordinator = $user->equipmentAreas->contains($maintainerGroup->equipmentArea);

        return $isMaintainer || $isAreaCoordinator;
    }

    /**
     * Determine whether the user can delete the maintainer group.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\MaintainerGroup  $maintainerGroup
     * @return mixed
     */
    public function delete(User $user, MaintainerGroup $maintainerGroup)
    {
        $isMaintainer = $maintainerGroup->maintainers->contains($user);
        $isAreaCoordinator = $user->equipmentAreas->contains($maintainerGroup->equipmentArea);

        return $isMaintainer || $isAreaCoordinator;
    }

    /**
     * Determine whether the user can restore the maintainer group.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\MaintainerGroup  $maintainerGroup
     * @return mixed
     */
    public function restore(User $user, MaintainerGroup $maintainerGroup)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the maintainer group.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\MaintainerGroup  $maintainerGroup
     * @return mixed
     */
    public function forceDelete(User $user, MaintainerGroup $maintainerGroup)
    {
        return false;
    }
}
