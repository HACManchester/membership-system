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
        $user->changeSubscriptionAmount($request->pence());

        \FlashNotification::success('Details Updated');
        return \Redirect::route('account.show', [$user->id]);
    }
}
