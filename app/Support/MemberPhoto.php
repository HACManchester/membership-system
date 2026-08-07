<?php

namespace BB\Support;

use BB\Helpers\UserImage;

class MemberPhoto
{
    /**
     * Resolve a member's profile photo URL, honouring the private-photo rules:
     * an anonymous placeholder unless the viewer is allowed to see protected photos.
     *
     * @param  mixed  $profilePhoto  Whether the member has a profile photo.
     * @param  mixed  $isPrivate     Whether that photo is marked private.
     * @param  string  $hash         The member's image hash.
     */
    public static function url($profilePhoto, $isPrivate, $hash): string
    {
        if (! $profilePhoto) {
            return UserImage::anonymous();
        }

        $user = \Auth::user();
        if ($isPrivate && (! $user || ! $user->shouldMemberSeeProtectedPhoto())) {
            return UserImage::anonymous();
        }

        return UserImage::thumbnailUrl($hash);
    }
}
