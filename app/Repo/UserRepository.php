<?php

namespace BB\Repo;

use BB\Entities\Gift;
use BB\Entities\User;
use BB\Support\CurrentUser;
use BB\Support\MemberStatus;
use BB\Support\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class UserRepository extends DBRepository
{

    /**
     * @var User
     */
    protected $model;
    /**
     * @var AddressRepository
     */
    private $addressRepository;
    /**
     * @var ProfileDataRepository
     */
    private $profileDataRepository;
    /**
     * @var \BB\Repo\PaymentRepository
     */
    private $paymentRepository;

    public function __construct(User $model, AddressRepository $addressRepository, ProfileDataRepository $profileDataRepository)
    {
        $this->model = $model;
        $this->perPage = 150;
        $this->addressRepository = $addressRepository;
        $this->profileDataRepository = $profileDataRepository;
        $this->paymentRepository = \App::make('\BB\Repo\PaymentRepository');
    }

    /**
     * @param integer $id
     * @return User
     */
    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function getActive()
    {
        return $this->model->active()->get();
    }

    public function getBillableActive()
    {
        return $this->model->active()->notSpecialCase()->get();
    }

    public function getPaginated(array $params)
    {
        $model = $this->model->with('roles')->with('profile')->withCount('keyFobs');

        if ($params['filter']) {
            // Grouped, so the online-only and left filters below apply to every match
            $model = $model
                ->where(function (Builder $query) use ($params) {
                    $query->where('email', 'like', '%' . $params['filter'] . '%')
                        ->orWhere('given_name', 'like', '%' . $params['filter'] . '%')
                        ->orWhere('family_name', 'like', '%' . $params['filter'] . '%')
                        ->orWhere('display_name', 'like', '%' . $params['filter'] . '%')
                        ->orWhere('announce_name', 'like', '%' . $params['filter'] . '%')
                        ->orWhereHas('keyFobs', function (Builder $q) use ($params) {
                            $q->where('key_id', 'like', $params['filter']);
                        });
                })
                ->take($params['limit']);
        }

        if (!$params['include_online_only']) {
            $model = $model->where(function ($query) {
                $query->where('online_only', '!=', '1')
                    ->orWhereNull('online_only');
            });
        }

        if ($params['new_only']) {
            $model = $model->whereDate('created_at', '>', date('Y-m-d', strtotime("-14 day")));
        }

        if ($params['showLeft']) {
            $model = $model->where('status', MemberStatus::Left);
        } else {
            $model = $model->where('status', '!=', MemberStatus::Left);
        }

        if ($this->isSortable($params)) {
            return $model->orderBy($params['sortBy'], $params['direction'])->simplePaginate($this->perPage);
        }
        return $model->simplePaginate($this->perPage);
    }


    /**
     * Return a collection of members for public display
     * @param bool $showPrivateMembers Some members don't want to listed on public pages, set to true to show everyone
     */
    public function getActivePublicList($showPrivateMembers = false)
    {
        if ($showPrivateMembers) {
            return \DB::table('users')
                ->join('profile_data', 'users.id', '=', 'profile_data.user_id')
                ->where('status', MemberStatus::Active)
                ->orderBy('profile_photo', 'desc')
                ->orderBy('display_name')
                ->get();
        } else {
            return \DB::table('users')
                ->join('profile_data', 'users.id', '=', 'profile_data.user_id')
                ->where('status', MemberStatus::Active)
                ->where('profile_private', 0)
                ->orderBy('profile_photo', 'desc')
                ->orderBy('display_name')
                ->get();
        }
    }

    /**
     * Get a list of active members suitable for use in a dropdown
     * @return array
     */
    public function getAllAsDropdown()
    {
        $members = $this->getActive();
        $memberDropdown = [];
        foreach ($members as $member) {
            $memberDropdown[$member->id] = $member->name;

            if (!$member->suppress_real_name) {
                $memberDropdown[$member->id] .= " ({$member->given_name} {$member->family_name})";
            }
        }
        return $memberDropdown;
    }

    /**
     * Returns a list of members we want to send newsletters to
     * 
     * This is scoped to users who:
     * - Have not opted out of newsletters
     * - Have an active membership, or their membership lapsed witihn the last 6 months
     */
    public function getWantNewsletter()
    {
        return $this->model
            ->NewsletterOptIns()
            ->where(function ($query) {
                $query->active()
                    ->orWhere(function ($query) {
                        $query->recentlyLapsed();
                    });
            })
            ->get();
    }

    /**
     * @param boolean $isAdminCreating Is the user making the change an admin
     * @return User
     */
    public function registerMember(array $memberData, $isAdminCreating)
    {
        if (empty($memberData['profile_photo_private'])) {
            $memberData['profile_photo_private'] = false;
        }

        if (empty($memberData['password'])) {
            unset($memberData['password']);
        }

        $memberData['rules_agreed'] = $memberData['rules_agreed'] ? Carbon::now() : null;

        // Sign up to newsletter by default (legitimate interest comms to members)
        $memberData['newsletter'] = true;

        // The address, photo and gift fields aren't user columns: they're saved below and by the controller
        $user = $this->model->newInstance(Arr::except($memberData, ['address', 'profile_photo_private', 'new_profile_photo', 'gift_code']));
        $user->hash = Str::random(30);
        $user->save();
        $this->profileDataRepository->createProfile($user->id);
        $this->addressRepository->saveUserAddress($user->id, $memberData['address'], $isAdminCreating);

        if (!empty($memberData['gift_code'])) {
            $gift_record = Gift::where('code', $memberData['gift_code'])->first();

            if ($gift_record) {
                $giftExpiry = Carbon::now()->addMonths((int) $gift_record->months)->startOfDay();
                $user->subscription_expires = $giftExpiry;
                $user->gift_expires = $giftExpiry;
                // gifts.credit is still in pounds (as a float); balances and payments are pence
                $creditPence = (int) round($gift_record->credit * 100);
                $user->cash_balance = $creditPence;
                $user->status = MemberStatus::Active;
                $user->active = true;
                $user->gift = $memberData['gift_code'];
                $user->save();

                // log cash payment
                $this->paymentRepository->recordPayment("balance", $user->id, 'Gift Certificate', null, $creditPence, PaymentStatus::Paid, 0, $memberData['gift_code']);

                $gift_record->delete();
            }
        }

        return $user;
    }

    /**
     * @param integer $userId           The ID of the user to be updated
     * @param boolean $isAdminUpdating  Is the user making the change an admin
     */
    public function updateMember($userId, array $recordData, $isAdminUpdating)
    {
        //If the password field hasn't been filled in unset it so it doesn't get set to a blank password
        if (empty($recordData['password'])) {
            unset($recordData['password']);
        }

        //Update the main user record; the address has its own table
        $this->update($userId, Arr::except($recordData, ['address']));

        //Update the user address
        if (isset($recordData['address']) && is_array($recordData['address'])) {
            $this->addressRepository->updateUserAddress($userId, $recordData['address'], $isAdminUpdating);
        }
    }

    public function recordGoCardlessMandateDetails($userId, $subscriptionId)
    {
        /** @var User $user */
        $user = $this->getById($userId);
        $user->mandate_id          = $subscriptionId;
        $user->gocardless_setup_id = null;
        $user->save();
    }

    public function updateUserPaymentMethod($userId, $paymentMethod, $paymentDay = null)
    {
        /** @var User $user */
        $user = $this->getById($userId);
        $user->payment_method = $paymentMethod;
        if ($paymentDay) {
            $user->payment_day = $paymentDay;
        }
        $user->save();
    }

    public function recordGoCardlessSubscription($userId, $subscriptionId, $paymentDay = null)
    {
        /** @var User $user */
        $user = $this->getById($userId);
        if ($paymentDay) {
            $user->payment_day = $paymentDay;
        }
        $user->subscription_id = $subscriptionId;
        $user->save();
    }

    /**
     * Record the fact that the user has agreed to the member induction and the rules
     *
     * @param $userId
     */
    public function recordInductionCompleted($userId)
    {
        $user = $this->getById($userId);

        $user->induction_completed = true;
        $user->inducted_by = CurrentUser::get()->id;

        $user->rules_agreed = $user->rules_agreed ? $user->rules_agreed : Carbon::now();

        $user->save();
    }
}
