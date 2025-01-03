<?php namespace BB\Presenters;

use Carbon\Carbon;
use Laracasts\Presenter\Presenter;

/**
 * @property-read string $monthlySubscription
 * @property-read string $paymentMethod
 */
class UserPresenter extends Presenter
{
    public function paymentMethod()
    {
        switch ($this->entity->payment_method) {
            case 'gocardless':
            case 'gocardless-variable':
                return 'Direct Debit';

            case 'standing-order':
                return 'Standing Order';

            case '':
                return '-';
        }
        return $this->entity->payment_method;
    }

    public function subscriptionExpiryDate()
    {
        if ($this->entity->subscription_expires && $this->entity->subscription_expires->year > 0) {
                    return $this->entity->subscription_expires->toFormattedDateString();
        } else {
                    return '-';
        }

    }

    public function cashBalance()
    {
        return '£' . number_format(($this->entity->cash_balance / 100), 2);
    }

    public function monthlySubscription()
    {
        return '£' . number_format(round($this->entity->monthly_subscription), 2);
    }

    public function subscriptionDetailLine()
    {
        if ($this->entity->status == 'setting-up') {
            return '';
        }
        $string = '' . $this->monthlySubscription . '/mo ';

        if ($this->paymentMethod) {
            $string .= 'by ' . $this->paymentMethod;
        }

        if ($this->entity->payment_day) {
            $string .= ' on the ' . $this->dayOfMonth();
        }

        return $string;
    }

    public function dayOfMonth()
    {
        $date = Carbon::now();
        $date->day = $this->entity->payment_day;
        return $date->format('jS');
    }

} 