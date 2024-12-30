<?php

namespace BB\Http\Requests;

use BB\Entities\Induction;
use BB\Entities\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreInductionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $userAwaitingTraining = null;
        if ($this->has('user_id')) {
            $userAwaitingTraining = User::find($this->input('user_id'));
        }

        return $this->user()->can('create', [
            Induction::class,
            $this->route('equipment'),
            $userAwaitingTraining
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => ['exists:BB\Entities\User,id'],
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
