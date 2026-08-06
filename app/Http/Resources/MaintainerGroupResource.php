<?php

namespace BB\Http\Resources;

use BB\Helpers\UserImage;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \BB\Entities\MaintainerGroup
 */
class MaintainerGroupResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'equipment_area' => $this->whenLoaded('equipmentArea', function () {
                return $this->equipmentArea ? [
                    'id' => $this->equipmentArea->id,
                    'name' => $this->equipmentArea->name,
                    'url' => route('equipment_area.show', $this->equipmentArea->slug, false),
                ] : null;
            }),
            'maintainers' => $this->whenLoaded('maintainers', function () {
                return $this->maintainers->map(function ($user) {
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
            'equipment' => $this->whenLoaded('equipment', function () {
                return $this->equipment->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'url' => route('equipment.show', $item->slug, false),
                    ];
                })->values();
            }),
            'equipment_count' => $this->equipment_count ?? 0,
            'urls' => [
                'show' => route('maintainer_groups.show', $this->slug, false),
            ],
        ];
    }
}
