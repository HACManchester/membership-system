<?php

namespace BB\Http\Requests\Admin;

use BB\Entities\AccessLockdown;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccessLockdownRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', AccessLockdown::class);
    }

    public function rules()
    {
        return [
            'reason' => ['nullable', 'string', 'max:255'],

            // At least one role is required: an empty list would shut everyone out,
            // admins included, and send the door system a header-only export.
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ];
    }
}
