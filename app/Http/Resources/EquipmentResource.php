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
        // Check if user is trained for this equipment (through any associated course)
        $isUserTrained = false;
        if (auth()->check() && $this->relationLoaded('courses')) {
            $inductionRepo = app(\BB\Repo\InductionRepository::class);
            
            foreach ($this->courses as $course) {
                $userInduction = $inductionRepo->getUserForCourse(auth()->user()->id, $course->id);
                if ($userInduction && $userInduction->trained) {
                    $isUserTrained = true;
                    break;
                }
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'working' => $this->working,
            'permaloan' => $this->permaloan,
            'dangerous' => $this->dangerous,
            'lone_working' => $this->lone_working,
            'room' => $this->room,
            'room_display' => RoomOptions::getDisplayName($this->room),
            'ppe' => $this->present()->ppeLabels(),
            'photo_url' => $this->hasPhoto() ? $this->getPhotoUrl(0) : null,
            'induction_category' => $this->induction_category,
            
            // Only include access code if user is trained for this equipment
            'access_code' => $this->when(
                $isUserTrained && $this->access_code,
                $this->access_code
            ),
            
            'urls' => [
                'show' => route('equipment.show', $this->slug, false),
            ],
        ];
    }
}
