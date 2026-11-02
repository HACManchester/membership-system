<?php

namespace BB\Http\Requests\Account;

use BB\Entities\User;
use BB\Support\CurrentUser;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A member editing their public profile. The photo-approval flags
 * (profile_photo, profile_photo_on_wall) are admin-only and deliberately absent.
 */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return CurrentUser::get()->can('updateProfile', User::findOrFail($this->route('account')));
    }

    public function rules(): array
    {
        return [
            'twitter' => ['nullable', 'max:50'],
            'facebook' => ['nullable', 'max:50'],
            'google_plus' => ['nullable', 'max:50'],
            'github' => ['nullable', 'max:50'],
            'irc' => ['nullable', 'max:50'],
            'website' => ['nullable', 'url', 'max:100'],
            'tagline' => ['nullable', 'max:250'],
            'description' => ['nullable'],
            'skills' => ['array'],
            'profile_photo_private' => ['nullable'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Trim so the length and url rules don't choke on stray spaces
        $this->merge(array_map(
            fn ($value) => is_string($value) ? trim($value) : $value,
            $this->except('new_profile_photo'),
        ));
    }
}
