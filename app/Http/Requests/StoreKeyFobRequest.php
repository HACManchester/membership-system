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
        if (!$this->user()->can('create', [KeyFob::class, $this->user])) {
            return false;
        }

        if ($this->user()->online_only || !$this->user()->induction_completed) {
            return false;
        }

        return true;
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
