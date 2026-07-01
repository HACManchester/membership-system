<?php

namespace BB\Http\Requests;

use BB\Entities\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreGoCardlessPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('makePayment', $this->targetUser());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // The only use of this endpoint is retrying a subscription payment
            'reason' => ['required', 'in:subscription'],
            // Amount arrives in pence; GoCardless' minimum transaction is £1
            'amount' => ['required', 'integer', 'min:100'],
        ];
    }

    /**
     * @param \Illuminate\Validation\Validator $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // A subscription payment settles the outstanding charge in full whatever
            // its size, so anything under the member's chosen amount is an underpayment
            $minimum = $this->targetUser()->monthly_subscription * 100;
            if (is_numeric($this->input('amount')) && $this->input('amount') < $minimum) {
                $validator->errors()->add('amount', 'Subscription payments must cover your monthly amount.');
            }
        });
    }

    public function targetUser(): User
    {
        return User::findOrFail($this->route('account'));
    }
}
