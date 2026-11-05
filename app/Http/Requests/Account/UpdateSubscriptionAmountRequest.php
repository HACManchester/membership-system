<?php

namespace BB\Http\Requests\Account;

use BB\Entities\User;
use BB\Helpers\MembershipPayments;
use BB\Support\CurrentUser;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A member changing how much they pay each month. The amount is whole pounds,
 * as at signup; only admins may set it below the configured minimum.
 */
class UpdateSubscriptionAmountRequest extends FormRequest
{
    private ?User $targetUser = null;

    public function authorize(): bool
    {
        return CurrentUser::get()->can('manage', $this->targetUser());
    }

    public function rules(): array
    {
        return [
            'monthly_subscription' => ['required', 'integer', 'min:' . $this->minimumPounds()],
        ];
    }

    public function messages(): array
    {
        $belowMinimum = CurrentUser::get()->isAdmin()
            ? 'Please enter a valid amount in pounds.'
            : sprintf(
                'The minimum subscription is %s, please contact the board for a lower amount. %s',
                MembershipPayments::formatPrice(MembershipPayments::getMinimumPrice()),
                // TODO: Lift this into some sort of "contact" config?
                'board@hacman.org.uk',
            );

        return [
            'monthly_subscription.required' => 'Please enter a valid amount in pounds.',
            'monthly_subscription.integer' => 'Please enter a whole number of pounds.',
            'monthly_subscription.min' => $belowMinimum,
        ];
    }

    public function targetUser(): User
    {
        return $this->targetUser ??= User::findOrFail((string) $this->route('account'));
    }

    /**
     * The configured minimum is in pence; the amount is whole pounds, so round
     * up to keep a part-pound minimum from letting a lower amount through.
     */
    private function minimumPounds(): int
    {
        if (CurrentUser::get()->isAdmin()) {
            return 0;
        }

        return (int) ceil(MembershipPayments::getMinimumPrice() / 100);
    }
}
