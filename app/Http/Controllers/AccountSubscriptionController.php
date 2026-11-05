<?php

namespace BB\Http\Controllers;

use BB\Http\Requests\Account\UpdateSubscriptionAmountRequest;
use Illuminate\Http\RedirectResponse;

class AccountSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:member');
    }

    public function update(UpdateSubscriptionAmountRequest $request): RedirectResponse
    {
        $user = $request->targetUser();
        // Validated as a whole number of pounds, but it arrives as a form string
        $user->changeSubscriptionAmount((int) $request->validated('monthly_subscription'));

        \FlashNotification::success('Details Updated');
        return \Redirect::route('account.show', [$user->id]);
    }
}
