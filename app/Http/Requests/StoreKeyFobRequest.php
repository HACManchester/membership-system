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
            'key_id' => ['required', 'unique:key_fobs', 'min:8', 'max:12', 'regex:/^([a-fA-F0-9]+)$/i'],
        ];
    }
}
