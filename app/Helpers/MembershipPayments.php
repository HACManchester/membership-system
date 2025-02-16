<?php

namespace BB\Helpers;

use BB\Data\MembershipPriceOption;
use Carbon\Carbon;

class MembershipPayments
{
    /**
     * Fetch the date of the users last subscription payment
     *
     * @param $userId
     * @return false|Carbon
     */
    public static function lastUserPaymentDate($userId)
    {
        /** @var \BB\Repo\SubscriptionChargeRepository */
        $subscriptionChargeRepository = \App::make('BB\Repo\SubscriptionChargeRepository');
        $latestCharge = $subscriptionChargeRepository->getMembersLatestPaidCharge($userId);

        if ($latestCharge) {
            return $latestCharge->charge_date;
        }
        return false;
    }

    /**
     * Fetch the expiry date based on the users last sub payment
     *
     * @param $userId
     * @return false|Carbon
     */
    public static function lastUserPaymentExpires($userId)
    {
        $date = self::lastUserPaymentDate($userId);
        if ($date) {
            return $date->setTime(0, 0, 0)->addMonth();
        }

        return false;
    }

    /**
     * Get the date the users sub payment should be valid to
     *   This handles the different grace periods for the different payment methods.
     *
     * @param string $paymentMethod
     * @param Carbon $refDate Defaults to today as the ref point, this can be overridden
     * @return Carbon
     */
    public static function getSubGracePeriodDate($paymentMethod, Carbon $refDate = null)
    {
        if (is_null($refDate)) {
            $refDate = Carbon::now();
        }

        //The time needs to be zeroed so that comparisons with pure dates work
        $refDate->setTime(0, 0, 0);

        $standingOrderCutoff      = $refDate->copy()->subMonths(6);
        $goCardlessCutoff         = $refDate->copy()->subDays(14);
        $goCardlessVariableCutoff = $refDate->copy()->subDays(10);
        $otherCutoff              = $refDate->copy()->subDays(7);

        if ($paymentMethod == 'gocardless') {
            return $goCardlessCutoff;
        } elseif ($paymentMethod == 'gocardless-variable') {
            return $goCardlessVariableCutoff;
        } elseif ($paymentMethod == 'standing-order') {
            return $standingOrderCutoff;
        } else {
            return $otherCutoff;
        }
    }

    /**
     * The minimum price for a membership to the space
     *
     * @return int Price in pence
     */
    public static function getMinimumPrice()
    {
        return config('membership.prices.minimum');
    }

    /**
     * The recommended price for a membership to the space
     *
     * @return int Price in pence
     */
    public static function getRecommendedPrice()
    {
        return config('membership.prices.recommended');
    }

    /**
     * The available price options for a membership to the space
     *
     * @return MembershipPriceOption[]
     */
    public static function getPriceOptions()
    {
        $options = config('membership.prices.options');
        $priceOptions = [];

        foreach ($options as $key => $option) {
            $priceOptions[] = new MembershipPriceOption(
                $option['title'],
                $option['description'],
                $option['value_in_pence']
            );
        }

        return $priceOptions;
    }

    /**
     * Format membership prices for consistent display across the website
     *
     * @param integer $pence
     * @return string
     */
    public static function formatPrice(int $pence)
    {
        return "£" . number_format($pence / 100, 2);
    }
}
