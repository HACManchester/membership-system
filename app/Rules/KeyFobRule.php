<?php

namespace BB\Rules;

use Illuminate\Contracts\Validation\Rule;

class KeyFobRule implements Rule
{
    public function passes($attribute, $value)
    {
        if (strlen($value) < 8 || strlen($value) > 12) {
            return false;
        }

        if (!preg_match('/^([a-fA-F0-9]+)$/i', $value)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'Invalid key fob ID.';
    }
}
