<?php

namespace BB\Http\Requests;

use BB\Entities\Induction;
use Illuminate\Foundation\Http\FormRequest;

class TrainInductionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('train', [Induction::class, $this->route('induction')]);
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
