<?php

namespace BB\Policies;

use BB\Entities\Role;
use BB\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

    public function view(User $user, Role $role)
    {
        return $user->isAdmin();
    }

    public function update(User $user, Role $role)
    {
        return $user->isAdmin();
    }
}
