<?php

namespace BB\Support;

use BB\Entities\User;

class DirectDebitSetup
{
    /**
     * Parameters for the GoCardless redirect flow that sets up a member's
     * Direct Debit, prefilled with what we know about them.
     *
     * Address fields are only sent when the member has an address on file:
     * some don't (left members coming back, sign-ups that never finished), and
     * GoCardless's hosted page asks for whatever isn't prefilled.
     */
    public static function redirectFlowParams(User $user): array
    {
        $customer = [
            'given_name' => $user->given_name,
            'family_name' => $user->family_name,
            'email' => $user->email,
        ];

        if ($address = $user->address) {
            $customer += [
                'address_line1' => $address->line_1,
                'address_line2' => $address->line_2,
                'city' => $address->line_3,
                'postal_code' => $address->postcode,
            ];
        }

        $customer['country_code'] = 'GB';

        return [
            'description' => 'Hackspace Manchester',
            'success_redirect_url' => str_replace('http://', 'https://', route('account.subscription.store', $user->id)),
            'session_token' => 'user-token-' . $user->id,
            'prefilled_customer' => $customer,
        ];
    }
}
