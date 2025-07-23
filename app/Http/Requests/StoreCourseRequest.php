<?php

namespace BB\Http\Requests;

use BB\Entities\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Course::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'unique:courses'],
            'slug' => ['required', 'unique:courses'],
            'description' => ['required'],
            'format' => ['required', Rule::in(Course::formatOptions()->keys())],
            'format_description' => ['present'],
            'frequency' => ['required', Rule::in(Course::frequencyOptions()->keys())],
            'frequency_description' => ['present'],
            'wait_time' => ['required'],
            'training_organisation_description' => ['nullable', 'string'],
            'schedule_url' => ['nullable', 'url'],
            'quiz_url' => ['nullable', 'url'],
            'request_induction_url' => ['nullable', 'url'],
            'equipment' => ['array'],
            'equipment.*' => ['integer', 'exists:equipment,id'],
            'paused' => ['boolean'],
        ];
    }
}
