<?php

namespace BB\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \BB\Entities\Room
 */
class RoomResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'equipment_count' => (int) ($this->equipment_count ?? 0),
            'urls' => [
                'show' => route('room.show', $this->slug, false),
            ],
        ];
    }
}
