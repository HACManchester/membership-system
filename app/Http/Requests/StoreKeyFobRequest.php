<?php

namespace BB\Http\Requests;

use BB\Entities\KeyFob;
use BB\Rules\KeyFobRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Unique;

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
            'key_id' => [
                'required_unless:type,access_code',
                new KeyFobRule,
                new Unique((new KeyFob)->getTable(), 'key_id'),
            ],
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
