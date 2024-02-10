<?php

namespace BB\Policies;

use BB\Entities\User;
use BB\Entities\KeyFob;
use Illuminate\Auth\Access\HandlesAuthorization;

class KeyFobPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the keyFob.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\KeyFob  $keyFob
     * @return mixed
     */
    public function view(User $currentUser, User $userBeingViewed)
    {
        return $currentUser->isAdmin() || $currentUser->id == $userBeingViewed->id;
    }

    /**
     * Determine whether the user can create keyFob.
     *
     * @param  \BB\Entities\User  $user
     * @return mixed
     */
    public function create(User $currentUser, User $userBeingViewed)
    {
        return $currentUser->isAdmin() || $currentUser->id == $userBeingViewed->id;
    }

    /**
     * Determine whether the user can delete the keyFob.
     *
     * @param  \BB\Entities\User  $user
     * @param  \BB\Entities\KeyFob  $keyFob
     * @return mixed
     */
    public function delete(User $currentUser, KeyFob $fob)
    {
        return $currentUser->isAdmin() || $currentUser->id == $fob->user_id;
    }
}
