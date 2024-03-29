<?php

namespace BB\Policies;

use BB\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function ban(User $authedUser, User $user)
    {
        // Can't ban yourself
        if ($authedUser->id == $user->id) {
            return false;
        }

        // Admins can ban others
        return $authedUser->isAdmin();
    }

    public function unban(User $authedUser, User $user)
    {
        // Admins can ban others
        return $authedUser->isAdmin();
    }
}
