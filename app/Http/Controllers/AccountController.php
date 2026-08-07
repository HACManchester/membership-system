<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Entities\Settings;

class AccountController extends Controller
{

    protected $layout = 'layouts.main';

    protected $userForm;

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

    /** @var \BB\Services\Credit */
    private $bbCredit;

    function __construct(
        \BB\Validators\UserValidator $userForm,
        \BB\Repo\EquipmentRepository $equipmentRepository,
        \BB\Repo\UserRepository $userRepository,
        \BB\Repo\AddressRepository $addressRepository,
        \BB\Repo\SubscriptionChargeRepository $subscriptionChargeRepository,
        \BB\Services\Credit $bbCredit
    ) {
        $this->userForm = $userForm;
        $this->equipmentRepository = $equipmentRepository;
        $this->userRepository = $userRepository;
        $this->addressRepository = $addressRepository;
        $this->subscriptionChargeRepository = $subscriptionChargeRepository;
        $this->bbCredit = $bbCredit;

        //This tones down some validation rules for admins
        $this->userForm->setAdminOverride(! \Auth::guest() && \Auth::user()->hasRole('admin'));

        $this->middleware('role:member');
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
