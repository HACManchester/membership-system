<?php

namespace BB\Support;

use BB\Entities\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

/**
 * The member who is signed in.
 *
 * Use this rather than Auth::user(). Auth::user() can also return an ApiKey,
 * because the door system's api guard signs in API keys, and it returns null
 * for guests. Code that assumes a member then fails with a 500.
 */
class CurrentUser
{
    /**
     * For code behind the auth middleware. A guest is sent to the login page
     * rather than getting an error.
     */
    public static function get(): User
    {
        $user = self::find();

        if (! $user) {
            throw new AuthenticationException();
        }

        return $user;
    }

    /**
     * For code that guests can reach too.
     */
    public static function find(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }
}
