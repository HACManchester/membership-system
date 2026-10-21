<?php

namespace BB\Http\Requests\Role;

use BB\Entities\Role;
use BB\Support\CurrentUser;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize()
    {
        /** @var Role $role */
        $role = $this->route('role');

        return CurrentUser::get()->can('update', $role);
    }

    public function rules()
    {
        // Note: name is intentionally omitted — it is the machine key roles are
        // matched by (hasRole(), role: middleware, equipment.managing_role_id) and
        // must stay immutable.
        return [
            'title' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'email_public' => ['nullable', 'string'],
            'email_private' => ['nullable', 'string'],
            'slack_channel' => ['nullable', 'string'],
            'members' => ['nullable', 'array'],
            'members.*' => ['exists:users,id'],
        ];
    }
}
