<?php namespace BB\Validators;

class InductionValidator extends FormValidator
{

    /**
     * Validation rules
     *
     * @var array
     */
    protected $rules = [
        'rules_agreed'          => 'accepted',
        'inductee_email'        => 'required|email'
    ];


} 