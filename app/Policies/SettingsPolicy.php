<?php

namespace BB\Policies;

use BB\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Settings hold secrets, such as the general induction code, so only admins
 * can see or change them.
 */
class SettingsPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user): bool
    {
        return $user->isAdmin();
    }
}
