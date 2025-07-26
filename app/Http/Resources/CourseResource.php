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
        $data = [
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
            'training_organisation_description' => $this->training_organisation_description,
            'schedule_url' => $this->schedule_url,
            'quiz_url' => $this->quiz_url,
            'request_induction_url' => $this->request_induction_url,
            'paused_at' => $this->paused_at,
            'is_paused' => $this->isPaused(),
            'equipment' => EquipmentResource::collection($this->whenLoaded('equipment')),
            'urls' => [
                'show' => route('courses.show', $this->slug, false),
            ],
        ];
        
        // Include user course induction status when user is authenticated
        if (auth()->check()) {
            $inductionRepo = app(\BB\Repo\InductionRepository::class);
            $userCourseInduction = $inductionRepo->getUserForCourse(auth()->user()->id, $this->id);
            $data['user_course_induction'] = $userCourseInduction ? new InductionResource($userCourseInduction) : null;
            
            // Include trainers for this course
            $trainers = $inductionRepo->getTrainersForCourse($this->id);
            $trainers->load(['user.profile']);
            $data['trainers'] = InductionResource::collection($trainers);
        }
        
        return $data;
    }
}
