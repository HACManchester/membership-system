<?php

namespace BB\Support;

/**
 * The settings the app knows about. Rows in the settings table whose key isn't
 * listed here are ignored, and only these can be edited from the admin area.
 */
enum SettingKey: string
{
    case GeneralInductionCode = 'general_induction_code';

    public function label(): string
    {
        return match ($this) {
            self::GeneralInductionCode => 'General induction code',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::GeneralInductionCode => 'New members enter this to prove they attended a general induction. '
                . 'Inducted members see it on the General Induction page so they can give inductions. '
                . 'Matched ignoring case and surrounding spaces.',
        };
    }

    /**
     * @return list<string>
     */
    public function rules(): array
    {
        return match ($this) {
            self::GeneralInductionCode => ['required', 'string', 'min:4', 'max:32', 'regex:/^[A-Za-z0-9-]+$/'],
        };
    }
}
