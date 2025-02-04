<?php

namespace BB\Policies;

use Auth;
use BB\Entities\User;
use BB\Entities\StorageBox;
use Illuminate\Auth\Access\HandlesAuthorization;

class StorageBoxPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->isAdmin() || $user->hasRole('storage')) {
            return true;
        }

        // fall through to policy methods
        return null;
    }

    /**
     * Determine whether the user can view the StorageBox.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\StorageBox  $StorageBox
     * @return mixed
     */
    public function view(User $user, StorageBox $StorageBox)
    {
        return true;
    }

    /**
     * Determine whether the user can create StorageBox.
     *
     * @param  \BB\Entities\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return Auth::user()->isAdmin();
    }

    /**
     * Determine whether the user can update the StorageBox.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\StorageBox  $StorageBox
     * @return mixed
     */
    public function update(User $user, StorageBox $StorageBox)
    {
        return Auth::user()->isAdmin();
    }

    /**
     * Determine whether the user can delete the StorageBox.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\StorageBox  $StorageBox
     * @return mixed
     */
    public function delete(User $user, StorageBox $StorageBox)
    {
        return Auth::user()->isAdmin();
    }

    public function canViewOld(User $user)
    {
        return false;
    }

    public function claim(User $user, StorageBox $StorageBox)
    {
        return false;
    }

    public function release(User $user, StorageBox $StorageBox)
    {
        return $user->id == $StorageBox->user_id || Auth::user()->isAdmin();
    }
}
