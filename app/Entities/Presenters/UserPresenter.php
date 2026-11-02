<?php

namespace BB\Entities\Presenters;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Display formatting for a member that several views share, kept out of
 * User.php to stop it growing.
 *
 * @property-read string $payment_method_label
 */
trait UserPresenter
{
    /**
     * How the member pays their subscription, e.g. "Direct Debit", or "-" when
     * no method is set.
     *
     * @return Attribute<non-empty-string, never>
     */
    protected function paymentMethodLabel(): Attribute
    {
        return Attribute::get(fn () => match ((string) $this->payment_method) {
            'gocardless', 'gocardless-variable' => 'Direct Debit',
            'standing-order' => 'Standing Order',
            '' => '-',
            default => (string) $this->payment_method,
        });
    }
}
