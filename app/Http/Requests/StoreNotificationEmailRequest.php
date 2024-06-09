<?php

namespace BB\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Controller is managing its own auth for now. Would be good to extract it later
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'subject'       => 'required',
            'message'       => 'required',
            'recipient'     => 'required',
            'send_to_all'   => 'boolean',
        ];
    }
}
