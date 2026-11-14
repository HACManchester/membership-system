<?php

namespace BB\Entities\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * A member's subscription settings: how and when they pay.
 */
trait HasSubscription
{
    /**
     * Days after the 28th become the 1st, so the payment day exists in every month.
     *
     * @return Attribute<int, int>
     */
    protected function paymentDay(): Attribute
    {
        return Attribute::set(fn ($value) => $value > 28 ? 1 : $value);
    }

    /**
     * Set how much the member pays each month, in pence.
     */
    public function changeSubscriptionAmount(int $pence): void
    {
        $this->monthly_subscription = $pence;
        $this->save();
    }

    /**
     * Is the user on a payment method that allows their subscription amount to be changed
     */
    public function canMemberChangeSubAmount(): bool
    {
        // TODO: Invert and pull from payment domain? Or drop it? Do we need to handle different payment methods?
        return in_array($this->attributes['payment_method'], ['gocardless-variable']);
    }
}
