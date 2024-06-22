<?php

namespace BB\Http\Requests;

use BB\Entities\KeyFob;
use Illuminate\Foundation\Http\FormRequest;

class StoreKeyFobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', [KeyFob::class, $this->user]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => ['required', 'in:keyfob,access_code'],
            'key_id' => ['required_unless:type,access_code', 'unique:key_fobs', 'min:8', 'max:12', 'regex:/^([a-fA-F0-9]+)$/i'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'key_id.required_unless' => 'Please enter the serial number of a key fob.',
        ];
    }
}
