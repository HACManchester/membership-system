<?php namespace BB\Validators;

use Illuminate\Validation\Validator;

class CustomValidator extends Validator
{
    /**
     * Laravel's `email` rule accepts an address with CR/LF in a quoted local
     * part (CVE-2026-48019), letting a submitted address inject headers into
     * mail sent to it. It's only fixed in Laravel 12.60 / 13.10, so reject CR/LF
     * here; every validator in the app is built through this class.
     */
    public function validateEmail($attribute, $value, $parameters)
    {
        if (is_string($value) && preg_match('/[\r\n]/', $value)) {
            return false;
        }

        return parent::validateEmail($attribute, $value, $parameters);
    }

    public function validatePostcode($attribute, $value, $parameters)
    {
        return preg_match('/^(GIR ?0AA|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]([0-9ABEHMNPRV-Y])?)|[0-9][A-HJKPS-UW]) ?[0-9][ABD-HJLNP-UW-Z]{2})$/i', $value);
    }
}