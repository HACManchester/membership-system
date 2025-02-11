<?php

namespace BB\Http\Requests;

use BB\Entities\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends StoreCourseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /** @var Course */
        $course = $this->route('course');
        return $this->user()->can('update', $course);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /** @var Course */
        $course = $this->route('course');

        return array_merge(
            parent::rules(),
            [
                'name' => ['required', Rule::unique('courses')->ignore($course->id)],
                'slug' => ['required', Rule::unique('courses')->ignore($course->id)],
            ]
        );
    }
}
