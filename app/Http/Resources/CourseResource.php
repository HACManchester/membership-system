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
            'training_organisation_description' => $this->training_organisation_description,
            'schedule_url' => $this->schedule_url,
            'quiz_url' => $this->quiz_url,
            'request_induction_url' => $this->request_induction_url,
            'paused_at' => $this->paused_at,
            'is_paused' => $this->isPaused(),
            'live' => $this->live,
            'equipment' => $this->whenLoaded('equipment', function () {
                return EquipmentResource::collection($this->equipment);
            }),

            // Include user course induction status when user is authenticated
            'user_course_training_record' => $this->when(auth()->check(), function () {
                $trainingRecordRepo = app(\BB\Repo\TrainingRecordRepository::class);
                $userCourseTrainingRecord = $trainingRecordRepo->getUserForCourse(auth()->user()->id, $this->id);
                return $userCourseTrainingRecord ? new TrainingRecordResource($userCourseTrainingRecord) : null;
            }),

            // Include trainers when user is authenticated
            'trainers' => $this->when(auth()->check(), function () {
                $trainingRecordRepo = app(\BB\Repo\TrainingRecordRepository::class);
                $trainers = $trainingRecordRepo->getTrainersForCourse($this->id);
                $trainers->load(['user.profile']);
                return TrainingRecordResource::collection($trainers);
            }),

            'urls' => [
                'show' => route('courses.show', $this->slug, false),
                'training' => route('courses.training.index', $this->slug, false),
            ],
        ];
    }
}
