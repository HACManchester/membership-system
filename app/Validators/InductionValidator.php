<?php

namespace BB\Validators;

class InductionValidator extends FormValidator
{
    /**
     * Validation rules
     *
     * @var array
     */
    protected $rules = [
        'induction_code' => 'required'
    ];
}
