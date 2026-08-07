<?php

namespace BB\Http\Resources;

use BB\Support\MemberPhoto;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A member's public profile page. Wraps the User (with its `profile` relation
 * set) and is handed the already-resolved skills list, which comes from a
 * separate repository intersection rather than a model relation.
 *
 * @mixin \BB\Entities\User
 */
class MemberProfileResource extends JsonResource
{
    /** @var array<int, array{name: string, icon: string}> */
    private $skills;

    /**
     * @param  \BB\Entities\User  $resource
     * @param  array<int, array{name: string, icon: string}>  $skills
     */
    public function __construct($resource, array $skills = [])
    {
        parent::__construct($resource);
        $this->skills = $skills;
    }

    public function toArray($request)
    {
        $profile = $this->profile;

        return [
            'name' => $this->name,
            'pronouns' => trim((string) $this->pronouns) !== '' ? $this->pronouns : null,
            'photo_url' => MemberPhoto::url($profile->profile_photo, $profile->profile_photo_private, $this->hash),
            'tagline' => $profile->present()->tagline,
            'description' => $profile->description,
            'links' => [
                'github' => $profile->present()->gitHubLink,
                'twitter' => $profile->present()->twitterLink,
                'telegram' => $profile->present()->googlePlusLink,
                'facebook' => $profile->present()->facebookLink,
                'website' => ! empty($profile->website) ? $profile->website : null,
            ],
            'irc' => ! empty($profile->irc) ? $profile->irc : null,
            'skills' => $this->skills,
        ];
    }
}
