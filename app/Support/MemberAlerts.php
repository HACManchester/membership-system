<?php

namespace BB\Support;

use BB\Entities\User;

/**
 * The things a member still needs to sort out on their account, shown on the
 * account page and counted on the sidebar's "Your Membership" badge.
 */
class MemberAlerts
{
    /**
     * @return list<'email-not-verified'|'missing-profile-photo'|'missing-phone'>
     */
    public static function for(User $user): array
    {
        $alerts = [];
        if (!$user->email_verified) {
            $alerts[] = 'email-not-verified';
        }
        $profile = $user->profile;
        if (!$profile || (!$profile->profile_photo && !$profile->new_profile_photo)) {
            $alerts[] = 'missing-profile-photo';
        }
        if (empty($user->phone)) {
            $alerts[] = 'missing-phone';
        }
        return $alerts;
    }
}
