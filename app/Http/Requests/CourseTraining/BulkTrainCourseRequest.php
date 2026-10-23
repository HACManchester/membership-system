<?php

namespace BB\Http\Requests\CourseTraining;

use BB\Support\CurrentUser;
use Illuminate\Foundation\Http\FormRequest;

class BulkTrainCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return CurrentUser::get()->can('train', $this->route('course'));
    }

    public function rules(): array
    {
        return [
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }
}
