<?php

namespace BB\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The full editable payload for the equipment create/edit form, including the
 * existing photos and the form's submit URLs.
 *
 * @mixin \BB\Entities\Equipment
 */
class EquipmentFormResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'manufacturer' => $this->manufacturer,
            'model_number' => $this->model_number,
            'room_id' => $this->room_id,
            'detail' => $this->detail,
            'description' => $this->description,
            'help_text' => $this->help_text,
            'docs' => $this->docs,
            'maintainer_group_id' => $this->maintainer_group_id,
            'working' => (bool) $this->working,
            'permaloan' => (bool) $this->permaloan,
            'permaloan_user_id' => $this->permaloan_user_id,
            'dangerous' => (bool) $this->dangerous,
            'lone_working' => (bool) $this->lone_working,
            'ppe' => $this->ppe,
            'access_fee' => $this->access_fee,
            'usage_cost' => $this->usage_cost,
            'usage_cost_per' => $this->usage_cost_per,
            'access_code' => $this->access_code,
            'admin_notes' => $this->admin_notes,

            // The associated induction course (equipment is one-course-per-page in practice).
            'course_id' => optional($this->courses->first())->id,

            // Legacy induction fields — retained only for records not yet on a course.
            'requires_induction' => (bool) $this->requires_induction,
            'induction_category' => $this->induction_category,
            'accepting_inductions' => (bool) $this->accepting_inductions,
            'induction_instructions' => $this->induction_instructions,
            'trainer_instructions' => $this->trainer_instructions,
            'trained_instructions' => $this->trained_instructions,

            'photos' => collect($this->photos)->map(function ($photo, $index) {
                return [
                    'index' => $index,
                    'url' => $this->getPhotoUrl($index),
                    'destroy_url' => route('equipment.photo.destroy', [$this->slug, $index], false),
                ];
            })->values(),

            'urls' => [
                'update' => route('equipment.update', $this->slug, false),
                'show' => route('equipment.show', $this->slug, false),
                'photo_store' => route('equipment.photo.store', $this->slug, false),
            ],
        ];
    }
}
