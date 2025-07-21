<?php

namespace BB\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \BB\Entities\Course
 */
class CourseResource extends JsonResource
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
            'description' => $this->description,
            'format' => [
                'label' => $this->present()->format,
                'value' => $this->format,
            ],
            'format_description' => $this->format_description,
            'frequency' => [
                'label' => $this->present()->frequency,
                'value' => $this->frequency,
            ],
            'frequency_description' => $this->frequency_description,
            'wait_time' => $this->wait_time,
            'paused_at' => $this->paused_at,
            'is_paused' => $this->isPaused(),
            'equipment' => EquipmentResource::collection($this->whenLoaded('equipment')),
            'urls' => [
                'show' => route('courses.show', $this->slug, false),
            ],
        ];
    }
}
