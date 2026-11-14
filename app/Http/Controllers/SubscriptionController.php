<?php namespace BB\Http\Controllers;

use Carbon\Carbon;
use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use BB\Repo\SubscriptionChargeRepository;
use BB\Services\MembershipStatus;
use GoCardlessPro\Core\Exception\InvalidStateException;
use Illuminate\Support\Facades\Log;
use BB\Support\DirectDebitSetup;

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

    function __construct(GoCardlessHelper $goCardless, SubscriptionChargeRepository $subscriptionChargeRepository, \BB\Repo\UserRepository $userRepository, private MembershipStatus $membershipStatus)
    {
        $this->goCardless = $goCardless;
        $this->subscriptionChargeRepository = $subscriptionChargeRepository;
        $this->userRepository = $userRepository;

        $this->middleware('role:member', array('only' => ['create', 'store', 'destroy']));
    }

    /**
     * Setup a new pre auth
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(string $userId)
    {
        $user = User::findOrFail($userId);
        $this->authorize('manage', $user);

        if(!$user->monthly_subscription) {
            \FlashNotification::error("Attempt to set up invalid direct debit, blocked.");
            return \Redirect::route('account.show', $user->id);
        }

        return \Redirect::to($this->goCardless->newPreAuthUrl($user, DirectDebitSetup::redirectFlowParams($user)));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(string $userId)
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

        $user = User::findOrFail($userId);
        $this->authorize('manage', $user);

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
        $this->membershipStatus->activate($user);

        return \Redirect::route('account.show', [$user->id]);
    }


    /**
     * Cancel a users subscription, used for all DD's
     *
     * @param  int  $id
     */
    public function destroy(string $userId, $id = null)
    {
        $user = User::findOrFail($userId);
        $this->authorize('manage', $user);
        if ($user->payment_method == 'gocardless') {
            try {
                $subscription = $this->goCardless->cancelSubscription($user->subscription_id);
                $cancelled = ($subscription->status == 'cancelled');
            } catch (InvalidStateException $e) {
                // Already cancelled at GoCardless; bring our record in line
                $cancelled = true;
            } catch (\Exception $e) {
                // GoCardless would keep collecting, so we can't record a cancellation
                Log::error($e);
                $cancelled = false;
            }

            if ($cancelled) {
                $this->membershipStatus->cancelSubscription($user);

                \FlashNotification::success('Your subscription has been cancelled');
                return \Redirect::back();
            }
        } elseif ($user->payment_method == 'gocardless-variable') {
            $status = $this->goCardless->cancelPreAuth($user->mandate_id);
            if ($status) {
                $this->membershipStatus->cancelSubscription($user);

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

    /**
     * Set up the experimental fixed GoCardless DD subscription for a member
     * (admin-only testing tool, separate from the normal monthly payment).
     */
    public function experimentalSubscriptionStore(string $userId)
    {
        $user = User::findOrFail($userId);
        $this->authorize('manage', $user);

        $subscription = $this->goCardless->createSubscription($user->mandate_id, $user->monthly_subscription, $user->payment_day, 'NEW-BBSUB' . $user->id);

        $this->userRepository->recordGoCardlessSubscription($user->id, $subscription->id);

        \FlashNotification::success('Details Updated');
        return \Redirect::route('account.show', [$user->id]);
    }

    /**
     * Cancel the experimental fixed GoCardless DD subscription for a member.
     */
    public function experimentalSubscriptionDestroy(string $userId)
    {
        $user = User::findOrFail($userId);
        $this->authorize('manage', $user);

        $this->goCardless->cancelSubscription($user->subscription_id);

        $this->userRepository->recordGoCardlessSubscription($user->id, null);

        \FlashNotification::success('Details Updated');
        return \Redirect::route('account.show', [$user->id]);
    }
}
