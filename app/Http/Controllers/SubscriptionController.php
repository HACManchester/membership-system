<?php namespace BB\Http\Controllers;

use Carbon\Carbon;
use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use BB\Repo\SubscriptionChargeRepository;

class SubscriptionController extends Controller
{

    /**
     * @var GoCardlessHelper $goCardless
     */
    protected $goCardless;

    /**
     * @var SubscriptionChargeRepository
     */
    protected $subscriptionChargeRepository;

    /**
     * @var \BB\Repo\UserRepository
     */
    protected $userRepository;

    function __construct(GoCardlessHelper $goCardless, SubscriptionChargeRepository $subscriptionChargeRepository, \BB\Repo\UserRepository $userRepository)
    {
        $this->goCardless = $goCardless;
        $this->subscriptionChargeRepository = $subscriptionChargeRepository;
        $this->userRepository = $userRepository;

        $this->middleware('role:member', array('only' => ['create', 'destroy']));
    }

    /**
     * Setup a new pre auth
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create($userId)
    {
        $user = User::findWithPermission($userId);

        if(!$user->monthly_subscription) {
            \FlashNotification::error("Attempt to set up invalid direct debit, blocked.");
            return \Redirect::route('account.show', $user->id);
        }

        $payment_details = array(
            "description"          => "Hackspace Manchester",
            'success_redirect_url' => str_replace('http://','https://',route('account.subscription.store', $user->id)),
            "session_token"        => 'user-token-'.$user->id,
            'prefilled_customer'   => [
                'given_name'    => $user->given_name,
                'family_name'   => $user->family_name,
                'email'         => $user->email,
                'address_line1' => $user->address->line_1,
                'address_line2' => $user->address->line_2,
                'city'          => $user->address->line_3,
                'postal_code'   => $user->address->postcode,
                'country_code'  => 'GB'
            ]
        );

        return \Redirect::to($this->goCardless->newPreAuthUrl($user, $payment_details));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store($userId)
    {
        $confirm_params = array(
            'resource_id'    => \Request::get('resource_id'),
            'resource_type'  => \Request::get('resource_type'),
            'resource_uri'   => \Request::get('resource_uri'),
            'signature'      => \Request::get('signature'),
        );

        // State is optional
        if (\Request::get('state')) {
            $confirm_params['state'] = \Request::get('state');
        }

        $user = User::findWithPermission($userId);

        try {
            $confirmed_resource = $this->goCardless->confirmResource($user, $confirm_params);
        } catch (\Exception $e) {
            \FlashNotification::error($e->getMessage());
            return \Redirect::route('account.show', $user->id);
        }


        if (!isset($confirmed_resource->links->mandate) || empty($confirmed_resource->links->mandate)) {
            \FlashNotification::error('Something went wrong, you can try again or get in contact');
            return \Redirect::route('account.show', $user->id);
        }

        // Save the mandate and complete the DD setup process
        $this->userRepository->recordGoCardlessMandateDetails($user->id, $confirmed_resource->links->mandate);

        // Setup the user for a variable DD payment on todays date
        $this->userRepository->updateUserPaymentMethod($user->id, 'gocardless-variable', Carbon::now()->day);

        // All we need for a valid member is an active DD so make sure the user account is active
        // If paying by variable DD take the first payment now
        $this->userRepository->ensureMembershipActive($user->id);

        return \Redirect::route('account.show', [$user->id]);
    }


    /**
     * Cancel a users subscription, used for all DD's
     *
     * @param  int  $id
     * @return Illuminate\Http\RedirectResponse
     */
    public function destroy($userId, $id = null)
    {
        /**
         * TODO: Check for and cancel pending sub charges
         */
        $user = User::findWithPermission($userId);
        if ($user->payment_method == 'gocardless') {
            try {
                $subscription = $this->goCardless->cancelSubscription($user->subscription_id);
                if ($subscription->status == 'cancelled') {
                    $user->cancelSubscription();
                    \FlashNotification::success('Your subscription has been cancelled');
                    return \Redirect::back();
                }
            } catch (\Exception $e) {
                $user->cancelSubscription();
                \FlashNotification::success('Your subscription has been cancelled');
                return \Redirect::back();
            }
        } elseif ($user->payment_method == 'gocardless-variable') {
            $status = $this->goCardless->cancelPreAuth($user->mandate_id);
            if ($status) {
                $user->mandate_id = null;
                $user->payment_method = '';
                $user->save();

                $user->setLeaving();

                $this->subscriptionChargeRepository->cancelOutstandingCharges($userId);

                \FlashNotification::success('Your direct debit has been cancelled');
                return \Redirect::back();
            }
        }
        \FlashNotification::error('Sorry, we were unable to cancel your subscription, please get in contact');
        return \Redirect::back();
    }


    public function listCharges()
    {
        $charges = $this->subscriptionChargeRepository->getChargesPaginated();
        return \View::make('payments.sub-charges')->with('charges', $charges);
    }
}
