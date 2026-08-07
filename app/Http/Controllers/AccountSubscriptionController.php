<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Exceptions\ValidationException;
use BB\Helpers\MembershipPayments;

class AccountSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:member');
    }

    /**
     * Update a member's monthly subscription amount.
     *
     * @param  int  $id
     */
    public function update($id)
    {
        $amount = \Request::input('monthly_subscription');

        if (!is_numeric($amount) || $amount < 0) {
            throw new ValidationException('Please enter a valid amount in pounds.');
        }
        $amount = (float) $amount;

        $minAmountPence = MembershipPayments::getMinimumPrice();
        $formattedMinAmount = MembershipPayments::formatPrice($minAmountPence);
        $minAmountPounds = $minAmountPence / 100;

        // TODO: Lift this into some sort of "contact" config?
        $boardEmail = 'board@hacman.org.uk';

        if ($amount < $minAmountPounds && !\Auth::user()->isAdmin()) {
            throw new ValidationException(sprintf('The minimum subscription is %s, please contact the board for a lower amount. %s', $formattedMinAmount, $boardEmail));
        }

        $user = User::findWithPermission($id);
        $user->updateSubAmount($amount);
        \FlashNotification::success('Details Updated');
        return \Redirect::route('account.show', [$user->id]);
    }
}
