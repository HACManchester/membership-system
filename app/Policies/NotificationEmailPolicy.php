<?php

namespace BB\Policies;

use BB\Entities\User;

/**
 * Who may email members. There's no model behind an email, so these are
 * registered as gate abilities in AuthServiceProvider.
 */
class NotificationEmailPolicy
{
    /**
     * Admins and anyone holding a role can open the mass email form.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->roles()->exists();
    }
}
