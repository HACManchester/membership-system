<?php

namespace BB\Http\Resources;

use BB\Support\RoomOptions;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \BB\Entities\Equipment
 */
class EquipmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'working' => $this->working,
            'permaloan' => $this->permaloan,
            'dangerous' => $this->dangerous,
            'room' => $this->room,
            'room_display' => RoomOptions::getDisplayName($this->room),
            'ppe' => $this->present()->ppeLabels(),
            'photo_url' => $this->hasPhoto() ? $this->getPhotoUrl(0) : null,
            'urls' => [
                'show' => route('equipment.show', $this->slug, false),
            ],
        ];
    }
}
