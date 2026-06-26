<?php

namespace BB\Http\Requests;

use BB\Entities\TrainingRecord;
use Illuminate\Foundation\Http\FormRequest;

class TrainTrainingRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('train', [TrainingRecord::class, $this->route('trainingRecord')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'trainer_user_id' => ['required', 'exists:BB\Entities\User,id'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }
}
