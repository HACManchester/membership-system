<?php

namespace BB\Http\Resources;

use BB\Helpers\UserImage;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \BB\Entities\EquipmentArea
 */
class EquipmentAreaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'area_coordinators' => $this->whenLoaded('areaCoordinators', function () {
                return $this->areaCoordinators->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'profile_photo_url' => ($user->relationLoaded('profile') && $user->profile->profile_photo)
                            ? UserImage::thumbnailUrl($user->hash)
                            : null,
                        'url' => route('members.show', $user->id, false),
                    ];
                })->values();
            }),
            'urls' => [
                'show' => route('equipment_area.show', $this->slug, false),
            ],
        ];
    }
}
