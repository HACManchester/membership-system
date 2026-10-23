<?php

namespace BB\Http\Requests\CourseTraining;

use BB\Support\CurrentUser;
use Illuminate\Foundation\Http\FormRequest;

class TrainCourseMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return CurrentUser::get()->can('train', $this->route('course'));
    }

    public function rules(): array
    {
        return [
            // Defaults to the signed-in trainer
            'trainer_user_id' => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }
}
