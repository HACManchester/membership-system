<?php

namespace BB\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \BB\Entities\Role
 */
class RoleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'email_public' => $this->email_public,
            'email_private' => $this->email_private,
            'slack_channel' => $this->slack_channel,
            'member_count' => $this->users_count ?? 0,
            'members' => $this->whenLoaded('users', function () {
                return $this->users->map(function ($user) {
                    return ['id' => $user->id, 'name' => $user->name];
                })->values();
            }),
            'urls' => [
                'edit' => route('roles.edit', $this->id, false),
            ],
        ];
    }
}
