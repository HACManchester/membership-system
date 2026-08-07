<?php

namespace BB\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateMemberRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * All fields are optional: the admin action-bar submits different subsets
     * from separate forms, so the controller applies whichever are present.
     */
    public function rules()
    {
        return [
            'trusted' => ['nullable'],
            'key_holder' => ['nullable'],
            'induction_completed' => ['nullable'],
            'profile_photo_on_wall' => ['nullable'],
            'active' => ['nullable'],
            'status' => ['nullable', 'string'],
            'subscription_expires' => ['nullable', 'date'],
        ];
    }
}
