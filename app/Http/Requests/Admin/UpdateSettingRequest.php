<?php

namespace BB\Http\Requests\Admin;

use BB\Entities\Settings;
use BB\Support\CurrentUser;
use BB\Support\SettingKey;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return CurrentUser::get()->can('update', Settings::class);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        /** @var SettingKey $settingKey */
        $settingKey = $this->route('settingKey');

        return [
            'setting_value' => $settingKey->rules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'setting_value.regex' => 'Use only letters, numbers and hyphens.',
        ];
    }
}
