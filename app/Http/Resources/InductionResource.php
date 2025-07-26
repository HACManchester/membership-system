<?php

namespace BB\Http\Resources;

use BB\Helpers\UserImage;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \BB\Entities\Induction
 * @property \BB\Entities\User|null $user
 * @property \BB\Entities\User|null $trainerUser
 * @property \BB\Entities\Course|null $course
 */
class InductionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'key' => $this->key,
            'trained' => $this->trained,
            'is_trainer' => $this->is_trainer,
            'created_at' => $this->created_at,
            'sign_off_requested_at' => $this->sign_off_requested_at,
            'sign_off_expires_at' => $this->getSignOffExpiryDate(),
        ];

        // Include user information when loaded
        if ($this->relationLoaded('user')) {
            $data['user'] = [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'pronouns' => $this->user->pronouns,
                'profile_photo_url' => $this->user->profile->profile_photo
                    ? UserImage::thumbnailUrl($this->user->hash)
                    : null,
            ];
        }

        // Include trainer information when loaded
        if ($this->relationLoaded('trainerUser')) {
            $data['trainer'] = [
                'id' => $this->trainerUser->id,
                'name' => $this->trainerUser->name,
            ];
        }

        if ($this->relationLoaded('course') && $this->relationLoaded('user')) {
            $data['urls'] = [
                'train' => route('courses.training.train', ['course' => $this->course, 'user' => $this->user], false),
                'untrain' => route('courses.training.untrain', ['course' => $this->course, 'user' => $this->user], false),
                'promote' => route('courses.training.promote', ['course' => $this->course, 'user' => $this->user], false),
                'demote' => route('courses.training.demote', ['course' => $this->course, 'user' => $this->user], false),
            ];
        }

        return $data;
    }
}
