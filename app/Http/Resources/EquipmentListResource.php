<?php

namespace BB\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes a piece of equipment for the Tools & Equipment listing — only the
 * fields the index card and its status badges render. The full detail/edit
 * payload lives in EquipmentResource.
 *
 * @mixin \BB\Entities\Equipment
 */
class EquipmentListResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $request->user();

        // Computed once for the whole collection in EquipmentController@index.
        $trained = (bool) $this->getAttribute('trained_for_user');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'requires_induction' => (bool) $this->requires_induction,
            'accepting_inductions' => (bool) $this->accepting_inductions,
            'working' => (bool) $this->working,
            'permaloan' => (bool) $this->permaloan,
            'dangerous' => (bool) $this->dangerous,
            'lone_working' => (bool) $this->lone_working,
            'photo_url' => $this->hasPhoto() ? $this->getPhotoUrl(0) : null,
            'room_display' => $this->roomDisplay(),
            'trained' => $trained,

            // Only reveal the access code to members trained on this equipment.
            'access_code' => $this->when($trained && $this->access_code, $this->access_code),

            'can' => [
                'update' => $user ? $user->can('update', $this->resource) : false,
            ],
            'urls' => [
                'show' => route('equipment.show', $this->slug, false),
                'edit' => route('equipment.edit', $this->slug, false),
            ],
        ];
    }

    private function roomDisplay(): ?string
    {
        return optional($this->roomModel)->name;
    }
}
