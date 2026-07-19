<?php

namespace BB\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterCourseInterestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('registerInterest', $this->route('course'));
    }

    public function rules(): array
    {
        return [];
    }
}
