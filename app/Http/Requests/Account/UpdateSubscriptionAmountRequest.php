<?php

namespace BB\Http\Requests\Account;

use BB\Entities\User;
use BB\Helpers\MembershipPayments;
use BB\Support\CurrentUser;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A member changing how much they pay each month. The form takes pounds with
 * up to two decimal places, stored as pence, up to £5,000; only admins may set
 * it below the configured minimum.
 */
class UpdateSubscriptionAmountRequest extends FormRequest
{
    private ?User $targetUser = null;

    public function authorize(): bool
    {
        return CurrentUser::get()->can('manage', $this->targetUser());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'monthly_subscription' => [
                'bail',
                'required',
                'numeric',
                'decimal:0,2',
                'min:0',
                // The pence migrations' limit, which admins are held to as well
                'max:5000',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($this->pence() < $this->minimumPence()) {
                        $fail($this->belowMinimumMessage());
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'monthly_subscription.required' => 'Please enter an amount in pounds, e.g. 22.50.',
            'monthly_subscription.numeric' => 'Please enter an amount in pounds, e.g. 22.50.',
            'monthly_subscription.decimal' => 'Please enter an amount in pounds, e.g. 22.50.',
            'monthly_subscription.min' => 'Please enter an amount in pounds, e.g. 22.50.',
            'monthly_subscription.max' => 'The most we can take each month is £5,000.',
        ];
    }

    public function targetUser(): User
    {
        return $this->targetUser ??= User::findOrFail((string) $this->route('account'));
    }

    /**
     * The validated amount, converted once from the pounds the form takes.
     */
    public function pence(): int
    {
        return (int) round((float) $this->input('monthly_subscription') * 100);
    }

    /**
     * The configured minimum, in pence like the amount; admins have none.
     */
    private function minimumPence(): int
    {
        return CurrentUser::get()->isAdmin() ? 0 : MembershipPayments::getMinimumPrice();
    }

    private function belowMinimumMessage(): string
    {
        return sprintf(
            'The minimum subscription is %s, please contact the board for a lower amount. %s',
            MembershipPayments::formatPrice(MembershipPayments::getMinimumPrice()),
            // TODO: Lift this into some sort of "contact" config?
            'board@hacman.org.uk',
        );
    }
}
