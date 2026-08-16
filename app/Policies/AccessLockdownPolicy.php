<?php

namespace BB\Policies;

use BB\Entities\AccessLockdown;
use BB\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccessLockdownPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

    public function create(User $user)
    {
        return $user->isAdmin();
    }

    public function delete(User $user, AccessLockdown $accessLockdown)
    {
        return $user->isAdmin();
    }
}
