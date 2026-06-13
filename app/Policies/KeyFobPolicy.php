<?php

namespace BB\Policies;

use BB\Entities\User;
use BB\Entities\KeyFob;
use Illuminate\Auth\Access\HandlesAuthorization;

class KeyFobPolicy
{
    use HandlesAuthorization;

    public function view(User $currentUser, User $userBeingViewed)
    {
        return $currentUser->isAdmin() || $currentUser->id == $userBeingViewed->id;
    }

    public function create(User $currentUser, User $userBeingViewed)
    {
        return $currentUser->isAdmin() || $currentUser->id == $userBeingViewed->id;
    }

    public function delete(User $currentUser, KeyFob $fob)
    {
        return $currentUser->isAdmin() || $currentUser->id == $fob->user_id;
    }
}
