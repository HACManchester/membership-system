<?php namespace BB\Validators;

class KeyFob extends FormValidator
{

    /**
     * Validation rules
     *
     * @var array
     */
    protected $rules = [
        'key_id' => ['required', 'unique:key_fobs','min:8','max:12','regex:/^([a-fA-F0-9]+)$/i'],
    ];

} 