<?php

namespace BB\Http\Resources;

use BB\Support\MemberPhoto;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A member as shown in the public members grid. Wraps the stdClass rows returned
 * by UserRepository::getActivePublicList (note: user_id, not id).
 *
 * @property int $user_id
 * @property string $display_name
 * @property string $hash
 * @property mixed $profile_photo
 * @property mixed $profile_photo_private
 */
class MemberCardResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->user_id,
            'name' => $this->display_name,
            'photo_url' => MemberPhoto::url($this->profile_photo, $this->profile_photo_private, $this->hash),
            'url' => route('members.show', $this->user_id, false),
        ];
    }
}
