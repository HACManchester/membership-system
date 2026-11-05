<?php

namespace BB\Http\Controllers;

use BB\Entities\Gift;
use BB\Helpers\MembershipPayments;
use BB\Support\CurrentUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class MemberRegistrationController extends Controller
{
    /**
     * @var \BB\Validators\UserValidator
     */
    private $userForm;
    /**
     * @var \BB\Helpers\UserImage
     */
    private $userImage;
    /**
     * @var \BB\Repo\UserRepository
     */
    private $userRepository;
    /**
     * @var \BB\Validators\ProfileValidator
     */
    private $profileValidator;

    public function __construct(
        \BB\Validators\UserValidator $userForm,
        \BB\Helpers\UserImage $userImage,
        \BB\Repo\UserRepository $userRepository,
        \BB\Validators\ProfileValidator $profileValidator
    ) {
        $this->userForm = $userForm;
        $this->userImage = $userImage;
        $this->userRepository = $userRepository;
        $this->profileValidator = $profileValidator;

        //This tones down some validation rules for admins
        $this->userForm->setAdminOverride((bool) CurrentUser::find()?->hasRole('admin'));
    }

    public function create()
    {
        //Is there a gift code?
        $gift = \Request::get('gift_certificate');
        $gift_code = \Request::get('gift_code');
        $gift_valid = false;
        $gift_details = array();

        // Check it is valid
        $gift_record = Gift::where('code', $gift_code)->first();

        if ($gift_record) {
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

    public function store()
    {
        $input = \Request::only(
            'given_name',
            'family_name',
            'email',
            'secondary_email',
            'display_name',
            'announce_name',
            'password',
            'phone',
            'address.line_1',
            'address.line_2',
            'address.line_3',
            'address.line_4',
            'address.postcode',
            'monthly_subscription',
            'emergency_contact',
            'new_profile_photo',
            'profile_photo_private',
            'rules_agreed',
            //'postFob',
            'gift_code',
            'pronouns',
            'suppress_real_name'
        );

        // Signup only creates full members. Set as 0, not false: UserValidator's required_if:online_only,0
        // rules don't match a boolean, so false would silently drop the full-member requirements.
        $input['online_only'] = 0;

        $this->userForm->validate($input);
        $this->profileValidator->validate($input);

        $user = $this->userRepository->registerMember($input, (bool) CurrentUser::find()?->hasRole('admin'));

        // A single file only: new_profile_photo[] arrives as an array
        $photo = \Request::file('new_profile_photo');
        if ($photo instanceof UploadedFile) {
            try {
                $this->userImage->uploadPhoto($user->hash, $photo->getRealPath(), true);

                $user->profile()->firstOrFail()->update(['new_profile_photo' => 1, 'profile_photo_private' => $input['profile_photo_private']]);
            } catch (\Exception $e) {
                Log::error($e);
            }
        }

        //If this isn't an admin user creating the record log them in
        if (! CurrentUser::find()?->isAdmin()) {
            \Auth::login($user);
        }

        return \Redirect::route('account.show', [$user->id]);
    }
}
