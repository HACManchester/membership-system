<?php

namespace BB\Rules;

use BB\Entities\Settings;
use Illuminate\Contracts\Validation\Rule;

class GeneralInductionCodeRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $actual_induction_code = Settings::get("general_induction_code");

        return trim(strtolower($value)) === trim(strtolower($actual_induction_code));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Incorrect general induction code given. Please try again.';
    }
}
