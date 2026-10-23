<?php

namespace BB\Http\Requests;

use BB\Support\CurrentUser;
use Illuminate\Foundation\Http\FormRequest;

class RegisterCourseInterestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return CurrentUser::get()->can('registerInterest', $this->route('course'));
    }

    public function rules(): array
    {
        return [];
    }
}
