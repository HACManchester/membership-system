<?php 

namespace BB\Http\Controllers;

use BB\Entities\Gift;
use BB\Entities\Notification;
use BB\Entities\User;
use BB\Entities\Settings;
use BB\Events\MemberGivenTrustedStatus;
use BB\Events\MemberPhotoWasDeclined;
use BB\Exceptions\ValidationException;
use BB\Helpers\MembershipPayments;
use BB\Mailer\UserMailer;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{

    protected $layout = 'layouts.main';

    protected $userForm;

    /**
     * @var \BB\Helpers\UserImage
     */
    private $userImage;
    /**
     * @var \BB\Validators\UserDetails
     */
    private $userDetailsForm;
    /**
     * @var \BB\Repo\ProfileDataRepository
     */
    private $profileRepo;
    /**
     * @var \BB\Repo\InductionRepository
     */
    private $inductionRepository;
    /**
     * @var \BB\Repo\EquipmentRepository
     */
    private $equipmentRepository;
    /**
     * @var \BB\Repo\UserRepository
     */
    private $userRepository;
    /**
     * @var \BB\Validators\ProfileValidator
     */
    private $profileValidator;
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
        \BB\Validators\UserDetails $userDetailsForm,
        \BB\Repo\ProfileDataRepository $profileRepo,
        \BB\Repo\InductionRepository $inductionRepository,
        \BB\Repo\EquipmentRepository $equipmentRepository,
        \BB\Repo\UserRepository $userRepository,
        \BB\Validators\ProfileValidator $profileValidator,
        \BB\Repo\AddressRepository $addressRepository,
        \BB\Repo\SubscriptionChargeRepository $subscriptionChargeRepository,
        \BB\Services\Credit $bbCredit)
    {
        $this->userForm = $userForm;
        $this->updateSubscriptionAdminForm = $updateSubscriptionAdminForm;
        $this->goCardless = $goCardless;
        $this->userImage = $userImage;
        $this->userDetailsForm = $userDetailsForm;
        $this->profileRepo = $profileRepo;
        $this->inductionRepository = $inductionRepository;
        $this->equipmentRepository = $equipmentRepository;
        $this->userRepository = $userRepository;
        $this->profileValidator = $profileValidator;
        $this->addressRepository = $addressRepository;
        $this->subscriptionChargeRepository = $subscriptionChargeRepository;
        $this->bbCredit = $bbCredit;

        //This tones down some validation rules for admins
        $this->userForm->setAdminOverride( ! \Auth::guest() && \Auth::user()->hasRole('admin'));

        $this->middleware('role:member', array('except' => ['create', 'createOnlineOnly', 'store']));
        $this->middleware('role:admin', array('only' => ['index']));
        //$this->middleware('guest', array('only' => ['create', 'store']));

        $paymentMethods = [
            'gocardless'    => 'GoCardless',
            'cash'          => 'Cash',
            'bank-transfer' => 'Manual Bank Transfer',
            'other'         => 'Other'
        ];
        \View::share('paymentMethods', $paymentMethods);
        \View::share('paymentDays', array_combine(range(1, 31), range(1, 31)));

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
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
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //Is there a gift code?
        $gift = \Request::get('gift_certificate');
        $gift_code = \Request::get('gift_code');
        $gift_valid = false;
        $gift_details = array();

        // Check it is valid
        $gift_record = Gift::where('code', $gift_code)->first();

        if($gift_record){
            $gift_valid = true;
            $gift_details = array(
                'from' => $gift_record->gifter_name,
                'to' => $gift_record->giftee_name,
                'months' => $gift_record->months,
                'credit' => $gift_record->credit
            );
        }


        $minAmount = MembershipPayments::getMinimumPrice();
        $recommendedAmount = MembershipPayments::getRecommendedPrice();
        $priceOptions = MembershipPayments::getPriceOptions();

        $confetti = $gift ? $gift_valid : true;

        \View::share('body_class', 'register_login');
        return \View::make('account.create', compact(
            'minAmount',
            'recommendedAmount',
            'priceOptions',
            'gift',
            'gift_code',
            'gift_valid',
            'gift_details',
            'confetti'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function createOnlineOnly()
    {
        \View::share('body_class', 'register_login');
        return \View::make('account.create-online-only');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function store()
    {
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
            'monthly_subscription',
            'custom_monthly_subscription',
            'emergency_contact',
            'new_profile_photo',
            'profile_photo_private',
            'rules_agreed',
            'visited_space',
            //'postFob',
            'gift_code',
            'pronouns',
            'suppress_real_name'
        );

        $this->userForm->validate($input);
        $this->profileValidator->validate($input);

        $user = $this->userRepository->registerMember($input, ! \Auth::guest() && \Auth::user()->hasRole('admin'));

        if (\Request::file('new_profile_photo')) {
            try {
                $this->userImage->uploadPhoto($user->hash, \Request::file('new_profile_photo')->getRealPath(), true);

                $this->profileRepo->update($user->id, ['new_profile_photo'=>1, 'profile_photo_private'=>$input['profile_photo_private']]);
            } catch (\Exception $e) {
                Log::error($e);
            }
        }

        //If this isn't an admin user creating the record log them in
        if (\Auth::guest() || ! \Auth::user()->isAdmin()) {
            \Auth::login($user);
        }

        return \Redirect::route('account.show', [$user->id]);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $user = User::findWithPermission($id);

        $inductions = $this->equipmentRepository->getRequiresInduction();

        // todo: make these variable names make sense
        $userInductions = $user->inductions()->get();
        foreach ($inductions as $i=>$induction) {
            $inductions[$i]->userInduction = false;
            foreach ($userInductions as $userInduction) {
                if ($userInduction->key == $induction->induction_category) {
                    $inductions[$i]->userInduction = $userInduction;
                }
            }
        }

        //get pending address if any
        $newAddress = $this->addressRepository->getNewUserAddress($id);

        //Get the member subscription payments
        $subscriptionCharges = $this->subscriptionChargeRepository->getMemberChargesPaginated($id);

        //Get the members balance
        $this->bbCredit->setUserId($user->id);
        $memberBalance = $this->bbCredit->getBalanceFormatted();

        $doorCode = Settings::get("emergency_door_key_storage_pin");

        return \View::make('account.show')
            ->with('user', $user)
            ->with('doorCode',$doorCode)
            ->with('inductions', $inductions)
            ->with('newAddress', $newAddress)
            ->with('subscriptionCharges', $subscriptionCharges)
            ->with('memberBalance', $memberBalance);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $user = User::findWithPermission($id);

        //We need to access the address here so its available in the view
        $user->address;

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
            if ( ! $user->trusted && \Request::input('trusted')) {
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
            $message = 'You have been made a trusted member at Hackspace Manchester';
            $notificationHash = 'trusted_status';
            Notification::logNew($user->id, $message, 'trusted_status', $notificationHash);
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
        $user = \Auth::user();
      
        if(!$user->email_verified){
            $userMailer = new UserMailer($user);
            $userMailer->sendConfirmationEmail();
            \FlashNotification::success('An email has been sent to your email address. Please click the link to confirm it.');
        }
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


    public function rejoin($id)
    {
        $user = User::findWithPermission($id);
        $user->rejoin();
        \FlashNotification::success('Details Updated');
        return \Redirect::route('account.show', [$user->id]);
    }

    public function updateSubscriptionAmount($id)
    {
        $amount = \Request::input('monthly_subscription');

        $minAmountPence = MembershipPayments::getMinimumPrice();
        $formattedMinAmount = MembershipPayments::formatPrice($minAmountPence);
        $minAmountPounds = $minAmountPence / 100;

        // TODO: Lift this into some sort of "contact" config?
        $boardEmail = 'board@hacman.org.uk';

        if ($amount < $minAmountPounds && !\Auth::user()->isAdmin()) {
            throw new ValidationException(sprintf('The minimum subscription is %s, please contact the board for a lower amount. %s', $formattedMinAmount, $boardEmail));
        }

        $user = User::findWithPermission($id);
        $user->updateSubAmount(\Request::input('monthly_subscription'));
        \FlashNotification::success('Details Updated');
        return \Redirect::route('account.show', [$user->id]);
    }
}
