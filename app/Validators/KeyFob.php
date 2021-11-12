<?php namespace BB\Validators;

class KeyFob extends FormValidator
{

    /**
     * Validation rules
     *
     * @var array
     */
    protected $rules = [
        'key_id' => 'required|unique:key_fobs|min:8|max:12|regex:^(ff[0-9]{8}|[a-f][a-eA-F0-9]+)$',
    ];

} 