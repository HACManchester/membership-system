<?php

namespace BB\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawCourseInterestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('withdrawInterest', $this->route('course'));
    }

    public function rules(): array
    {
        return [];
    }
}
