<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Mailer\UserMailer;

class EmailConfirmationController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:member');
    }

    public function confirmEmail($id, $hash)
    {
        $user = User::find($id);
        if ($user && $user->hash == $hash) {
            $user->emailConfirmed();
            \FlashNotification::success('Email address confirmed, thank you');
            return \Redirect::route('account.show', $user->id);
        }
        \FlashNotification::error('Error confirming email address');
        return \Redirect::route('home');
    }

    public function sendConfirmationEmail()
    {
        /** @var User $user */
        $user = \Auth::user();

        if (!$user->email_verified) {
            $userMailer = new UserMailer($user);
            $userMailer->sendConfirmationEmail();
            \FlashNotification::success('An email has been sent to your email address. Please click the link to confirm it.');
        }
        return \Redirect::route('account.show', [$user->id]);
    }
}
