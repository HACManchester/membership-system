<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Entities\Settings;
use BB\Events\MemberGivenTrustedStatus;
use BB\Events\MemberPhotoWasDeclined;
use BB\Exceptions\ValidationException;
use BB\Helpers\MembershipPayments;

class AccountController extends Controller
{

    protected $layout = 'layouts.main';

    protected $userForm;

    /**
     * @var \BB\Helpers\UserImage
     */
    private $userImage;
    /**
     * @var \BB\Repo\EquipmentRepository
     */
    private $equipmentRepository;
    /**
     * @var \BB\Repo\UserRepository
     */
    private $userRepository;
    /**
     * @var \BB\Repo\AddressRepository
     */
    private $addressRepository;
    /**
     * @var \BB\Repo\SubscriptionChargeRepository
     */
    private $subscriptionChargeRepository;

    /**
     * @var \BB\Helpers\GoCardlessHelper
     */
    private $goCardless;

    /** @var \BB\Validators\UpdateSubscription */
    private $updateSubscriptionAdminForm;

    /** @var \BB\Services\Credit */
    private $bbCredit;

    function __construct(
        \BB\Validators\UserValidator $userForm,
        \BB\Validators\UpdateSubscription $updateSubscriptionAdminForm,
        \BB\Helpers\GoCardlessHelper $goCardless,
        \BB\Helpers\UserImage $userImage,
        \BB\Repo\EquipmentRepository $equipmentRepository,
        \BB\Repo\UserRepository $userRepository,
        \BB\Repo\AddressRepository $addressRepository,
        \BB\Repo\SubscriptionChargeRepository $subscriptionChargeRepository,
        \BB\Services\Credit $bbCredit
    ) {
        $this->userForm = $userForm;
        $this->updateSubscriptionAdminForm = $updateSubscriptionAdminForm;
        $this->goCardless = $goCardless;
        $this->userImage = $userImage;
        $this->equipmentRepository = $equipmentRepository;
        $this->userRepository = $userRepository;
        $this->addressRepository = $addressRepository;
        $this->subscriptionChargeRepository = $subscriptionChargeRepository;
        $this->bbCredit = $bbCredit;

        //This tones down some validation rules for admins
        $this->userForm->setAdminOverride(! \Auth::guest() && \Auth::user()->hasRole('admin'));

        $this->middleware('role:member');
        $this->middleware('role:admin', array('only' => ['index']));

        $paymentMethods = [
            'gocardless'    => 'GoCardless',
            'cash'          => 'Cash',
            'bank-transfer' => 'Manual Bank Transfer',
            'other'         => 'Other'
        ];
        \View::share('paymentMethods', $paymentMethods);
        \View::share('paymentDays', array_combine(range(1, 31), range(1, 31)));
    }

    public function index()
    {
        $filter = \Request::get('filter');
        $include_online_only = \Request::get('include_online_only');
        $new_only = \Request::get('new_only');
        $sortBy = \Request::get('sortBy');
        $direction = \Request::get('direction', 'asc');
        $showLeft = \Request::get('showLeft', 0);
        $limit = \Request::get('limit');

        $users = $this->userRepository->getPaginated(compact('sortBy', 'direction', 'showLeft', 'filter', 'include_online_only', 'new_only', 'limit'));

        return \View::make('account.index')->with('users', $users);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id)
    {
        $user = User::findWithPermission($id);

        $equipmentRequiringInduction = $this->equipmentRepository->getRequiresInduction();
        $equipmentRequiringInduction->load('courses');

        // Attach the member's training record (if any) for each piece of equipment.
        // A record counts if it matches either training-linkage path: the modern
        // course relationship (course_id) or the legacy induction_category↔key match.
        $userTrainingRecords = $user->trainingRecords()->get();
        foreach ($equipmentRequiringInduction as $equipment) {
            $courseIds = $equipment->courses->pluck('id');
            $equipment->userTrainingRecord = $userTrainingRecords->first(function ($record) use ($equipment, $courseIds) {
                return ($record->course_id !== null && $courseIds->contains($record->course_id))
                    || (! empty($equipment->induction_category) && $record->key === $equipment->induction_category);
            }) ?? false;
        }

        //get pending address if any
        $newAddress = $this->addressRepository->getNewUserAddress($id);

        //Get the member subscription payments
        $subscriptionCharges = $this->subscriptionChargeRepository->getMemberChargesPaginated($id);

        //Get the members balance
        $this->bbCredit->setUserId($user->id);
        $memberBalance = $this->bbCredit->getBalanceFormatted();

        $doorCode = Settings::get("emergency_door_key_storage_pin");

        $hasSubscriptionPaymentsInProgress = $this->hasSubscriptionPaymentsInProgress($user);

        return \View::make('account.show')
            ->with('user', $user)
            ->with('doorCode', $doorCode)
            ->with('equipmentRequiringInduction', $equipmentRequiringInduction)
            ->with('newAddress', $newAddress)
            ->with('subscriptionCharges', $subscriptionCharges)
            ->with('memberBalance', $memberBalance)
            ->with('hasSubscriptionPaymentsInProgress', $hasSubscriptionPaymentsInProgress);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id)
    {
        $user = User::findWithPermission($id);

        // Make the address available in the view
        $user->load('address');

        return \View::make('account.edit')->with('user', $user);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id)
    {
        $user = User::findWithPermission($id);
        $input = \Request::only(
            'given_name',
            'family_name',
            'email',
            'secondary_email',
            'display_name',
            'announce_name',
            'online_only',
            'password',
            'phone',
            'address.line_1',
            'address.line_2',
            'address.line_3',
            'address.line_4',
            'address.postcode',
            'emergency_contact',
            'profile_private',
            'newsletter',
            'pronouns',
            'suppress_real_name'
        );

        // TODO: Move to proper validators and 'validated' output?
        if (!\Auth::user()->can('changeUsername', $user)) {
            unset($input['display_name']);
        }

        $this->userForm->validate($input, $user->id);

        $this->userRepository->updateMember($id, $input, \Auth::user()->hasRole('admin'));

        \FlashNotification::success('Details Updated');
        return \Redirect::route('account.show', [$user->id]);
    }



    public function adminUpdate($id)
    {
        $user = User::findWithPermission($id);

        $madeTrusted = false;

        if (\Request::has('trusted')) {
            if (! $user->trusted && \Request::input('trusted')) {
                //User has been made a trusted member
                $madeTrusted = true;
            }
            $user->trusted = \Request::input('trusted');
        }

        if (\Request::has('key_holder')) {
            $user->key_holder = \Request::input('key_holder');
        }

        if (\Request::has('induction_completed')) {
            $user->induction_completed = \Request::input('induction_completed');
        }

        if (\Request::has('profile_photo_on_wall')) {
            $profileData = $user->profile()->first();
            $profileData->profile_photo_on_wall = \Request::input('profile_photo_on_wall');
            $profileData->save();
        }

        if (\Request::has('photo_approved')) {
            $profile = $user->profile()->first();

            if (\Request::input('photo_approved')) {
                $this->userImage->approveNewImage($user->hash);
                $profile->update(['new_profile_photo' => false, 'profile_photo' => true]);
            } else {
                $profile->update(['new_profile_photo' => false]);
                event(new MemberPhotoWasDeclined($user));
            }
        }

        // Handle membership state fields
        if (\Request::has('active')) {
            $user->active = \Request::input('active');
        }
        if (\Request::has('status')) {
            $user->status = \Request::input('status');
        }
        if (\Request::has('subscription_expires')) {
            $expiryDate = \Request::input('subscription_expires');
            if (!empty($expiryDate)) {
                $user->subscription_expires = $expiryDate;
            }
        }

        $user->save();

        if (\Request::has('approve_new_address')) {
            if (\Request::input('approve_new_address') == 'Approve') {
                $this->addressRepository->approvePendingMemberAddress($id);
            } elseif (\Request::input('approve_new_address') == 'Decline') {
                $this->addressRepository->declinePendingMemberAddress($id);
            }
        }

        if ($madeTrusted) {
            event(new MemberGivenTrustedStatus($user));
        }

        if (\Request::has('experimental_dd_subscription')) {
            $subscription = $this->goCardless->createSubscription($user->mandate_id, $user->monthly_subscription * 100, $user->payment_day, 'NEW-BBSUB' . $user->id);

            $this->userRepository->recordGoCardlessSubscription($user->id,  $subscription->id);
        }
        if (\Request::has('cancel_experimental_dd_subscription')) {
            $this->goCardless->cancelSubscription($user->subscription_id);

            $this->userRepository->recordGoCardlessSubscription($user->id,  null);
        }


        if (\Request::wantsJson()) {
            return \Response::json('Updated', 200);
        } else {
            \FlashNotification::success('Details Updated');
            return \Redirect::route('account.show', [$user->id]);
        }
    }


    public function alterSubscription($id)
    {
        // I don't think this is used any more

        $user = User::findWithPermission($id);
        $input = \Request::all();

        $this->updateSubscriptionAdminForm->validate($input, $user->id);

        if (($user->payment_method == 'gocardless') && ($input['payment_method'] != 'gocardless')) {
            //Changing away from GoCardless
            $subscription = $this->goCardless->cancelSubscription($user->subscription_id);
            if ($subscription->status == 'cancelled') {
                $user->cancelSubscription();
            }
        }

        $user->updateSubscription($input['payment_method'], $input['payment_day']);

        \FlashNotification::success('Details Updated');
        return \Redirect::route('account.show', [$user->id]);
    }

    public function destroy($id)
    {
        $user = User::findWithPermission($id);

        // If they never became a member just delete the record
        if ($user->status == 'setting-up') {
            $user->delete();

            \FlashNotification::success('Member deleted');
            return \Redirect::route('account.index');
        }

        //No one will ever leaves the system but we can at least update their status to left.
        $user->setLeaving();

        \FlashNotification::success('Updated status to leaving');

        return \Redirect::route('account.show', [$user->id]);
    }

    public function updateSubscriptionAmount($id)
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

    private function hasSubscriptionPaymentsInProgress($user)
    {
        if ($user->payment_method !== 'gocardless-variable') {
            return false;
        }

        $outstandingPayments = $user->payments()
            ->subscription()
            ->whereIn('status', ['pending', 'pending_submission', 'processing'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $outstandingPayments->isNotEmpty();
    }
}
