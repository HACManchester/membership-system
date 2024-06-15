<?php

namespace BB\Entities;

use BB\Exceptions\AuthenticationException;
use BB\Traits\UserRoleTrait;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;
use Auth;
use Hash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 *
 * @property integer $id
 * @property string $email
 * @property string $display_name
 * @property string $name
 * @property string $given_name
 * @property string $announce_name
 * @property string $online_only
 * @property string $family_name
 * @property string $hash
 * @property bool $active
 * @property bool $key_holder
 * @property bool $trusted
 * @property bool $banned
 * @property bool $email_verified
 * @property bool $induction_completed
 * @property integer $inducted_by
 * @property integer $payment_day
 * @property string $status
 * @property string $payment_method
 * @property string $subscription_id
 * @property Carbon $subscription_expires
 * @property Carbon $banned_date
 * @property string $phone
 * @property integer $storage_box_payment_id
 * @property ProfileData $profile
 * @property string|null secondary_payment_method
 * @property string      mandate_id
 * @property int         monthly_subscription
 * @property string      gocardless_setup_id
 * @property bool   postFob (false=collect, true=post)
 * @property date last_seen
 * @package BB\Entities
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{

    use UserRoleTrait, PresentableTrait, Authenticatable, Authorizable, CanResetPassword, Notifiable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password', 'remember_token');

    protected $auditFields = array('induction_completed', 'trusted', 'key_holder');

    protected $presenter = 'BB\Presenters\UserPresenter';


    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'given_name',
        'family_name',
        'email',
        'secondary_email',
        'display_name',
        'announce_name',
        'online_only',
        'password',
        'emergency_contact',
        'phone',
        'monthly_subscription',
        'profile_private',
        'newsletter',
        'hash',
        'rules_agreed',
        'key_holder',
        'key_deposit_payment_id',
        'trusted',
        'induction_completed',
        'payment_method',
        'active',
        'status',
        'postFob',
        'gift',
        'seen_at',
        'pronouns',
        'banned',
        'banned_reason',
        'banned_date',
        'suppress_real_name',
    ];


    protected $attributes = [
        'status'                => 'setting-up',
        'active'                => 0,
        'key_holder'            => 0,
        'trusted'               => 0,
        'email_verified'        => 0,
        'founder'               => 0,
        'induction_completed'   => 0,
        'payment_day'           => 0,
        'profile_private'       => 0,
        'newsletter'            => 0,
        'cash_balance'          => 0,
    ];

    protected $casts = [
        'visited_forum' => 'boolean',

        // TODO: Enable these casts one at a time and verify casting will not break references
        // 'email_verified' => 'boolean',
        // 'active' => 'boolean',
        // 'founder' => 'boolean',
        // 'director' => 'boolean',
        // 'trusted' => 'boolean',
        // 'key_holder' => 'boolean',
        // 'induction_completed' => 'boolean',
        // 'profile_private' => 'boolean',
        'banned' => 'boolean',
        'suppress_real_name' => 'boolean',
    ];


    public function getDates()
    {
        return array('created_at', 'updated_at', 'subscription_expires', 'banned_date', 'rules_agreed', 'seen_at');
    }


    public static function statuses()
    {
        return [
            'setting-up'        => 'Setting Up',
            'active'            => 'Active',
            'payment-warning'   => 'Payment Warning',
            'suspended'         => 'Suspended',
            'leaving'           => 'Leaving',
            'on-hold'           => 'On Hold',
            'left'              => 'Left',
            'honorary'          => 'Honorary'
        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | The connections between this model and others
    |
    */

    public function payments()
    {
        return $this->hasMany(Payment::class)->orderBy('created_at', 'desc');
    }

    public function inductions()
    {
        return $this->hasMany(Induction::class);
    }

    public function keyFob()
    {
        return $this->hasMany(KeyFob::class)->where('active', true)->first();
    }

    public function keyFobs()
    {
        return $this->hasMany(KeyFob::class)->where('active', true);
    }

    public function profile()
    {
        return $this->hasOne(ProfileData::class);
    }

    public function address()
    {
        return $this->hasOne(Address::class)->orderBy('approved', 'asc');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    public function storageBoxes()
    {
        return $this->hasMany(StorageBox::class)->where('active', true);
    }



    /*
    |--------------------------------------------------------------------------
    | Attribute Getters and Setters and Model Extensions
    |--------------------------------------------------------------------------
    |
    | Useful properties and methods to have on a user model
    |
    */

    public function getNameAttribute()
    {
        return $this->attributes['display_name'];
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    public function setPaymentDayAttribute($value)
    {
        //Ensure the payment date will always exist on any month payment_date
        if ($value > 28) {
            $value = 1;
        }
        $this->attributes['payment_day'] = $value;
    }

    /**
     * Can the user see protected member photos?
     * Only available to active members
     *
     * @return bool
     */
    public function shouldMemberSeeProtectedPhoto()
    {
        switch ($this->attributes['status']) {
            case 'active':
            case 'payment-warning':
            case 'honorary':
                return true;
            default:
                return false;
        }
    }

    /**
     * Is the user on a payment method that allows their subscription amount to be changed
     *
     * @return bool
     */
    public function canMemberChangeSubAmount()
    {
        return in_array($this->attributes['payment_method'], ['gocardless-variable', 'balance']);
    }

    /**
     * Is the user part of the admin group
     *
     * @return bool
     */
    public function isAdmin()
    {
        return Auth::user()->hasRole('admin');
    }

    /**
     * Should GoCardless be promoted to the user
     *
     * @return bool
     */
    public function promoteGoCardless()
    {
        return (($this->payment_method != 'balance' && $this->payment_method != 'gocardless' && $this->payment_method != 'gocardless-variable') && ($this->status == 'active'));
    }

    /**
     * Should we be promoting the new variable gocardless to users?
     *
     * @return bool
     */
    public function promoteVariableGoCardless()
    {
        return (($this->status == 'active') && ($this->payment_method == 'gocardless'));
    }

    /**
     * Get an array of alerts for the user
     *
     * @return array
     */
    public function getAlerts()
    {
        $alerts = [];
        if (!$this->email_verified) {
            $alerts[] = 'email-not-verified';
        }
        if (!$this->profile->profile_photo && !$this->profile->new_profile_photo) {
            $alerts[] = 'missing-profile-photo';
        }
        if (empty($this->phone)) {
            $alerts[] = 'missing-phone';
        }
        return $alerts;
    }

    /**
     * @return bool
     */
    public function isBanned()
    {
        return $this->banned;
    }

    /**
     * @return bool
     */
    public function isSuspended()
    {
        return ($this->status == 'suspended');
    }

    /**
     * @return bool
     */
    public function isInducted()
    {
        return $this->induction_completed && $this->inducted_by;
    }

    public function inductedBy()
    {
        return User::findOrFail($this->inducted_by);
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where(['active' => true, 'banned' => false]);
    }

    public function scopeNotSpecialCase($query)
    {
        return $query->where('status', '!=', 'honorary')
            ->where(function ($subquery) {
                $subquery->whereNull('gift_expires')->orWhere('gift_expires', '<=', Carbon::today());
            });
    }

    public function scopeLeaving($query)
    {
        return $query->where('status', '=', 'leaving');
    }

    public function scopePaymentWarning($query)
    {
        return $query->where('status', '=', 'payment-warning');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', '=', 'suspended');
    }

    public function scopeRecentlyLapsed($query)
    {
        return $query->whereActive(false)
            ->where('banned', false)
            ->where('subscription_expires', '>=', Carbon::today()->subMonths(6));
    }

    public function scopeNewsletterOptIns($query)
    {
        return $query->where('newsletter', '=', true);
    }


    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @param $paymentMethod
     * @param $paymentDay
     * @deprecated
     */
    public function updateSubscription($paymentMethod, $paymentDay)
    {
        if ($paymentDay > 28) {
            $paymentDay = 1;
        }

        $this->attributes['payment_method'] = $paymentMethod;
        $this->attributes['payment_day']    = $paymentDay;

        $this->save();
    }

    /**
     * @param $amount
     * @deprecated
     */
    public function updateSubAmount($amount)
    {
        $this->attributes['monthly_subscription'] = $amount;
        $this->save();
    }

    /**
     * @deprecated
     */
    public function cancelSubscription()
    {
        $this->payment_method = '';
        $this->subscription_id = '';
        $this->mandate_id = '';
        $this->payment_day = '';
        $this->status = 'leaving';
        $this->save();
    }

    /**
     * @deprecated
     */
    public function setLeaving()
    {
        $this->status = 'leaving';
        $this->save();
    }

    /**
     * @deprecated
     */
    public function setSuspended()
    {
        $this->status = 'suspended';
        $this->active = false;
        $this->save();
    }

    /**
     * @deprecated
     */
    public function rejoin()
    {
        $this->status = 'setting-up';
        $this->save();
    }

    public function emailConfirmed()
    {
        $this->email_verified = true;
        $this->save();
    }

    public function emailChanging()
    {
        $this->email_verified = false;
    }

    public function markAsSeen()
    {
        $this->seen_at = date("Y-m-d H:i:s");
        $this->save();
    }

    /**
     * Fetch a user record, performs a permission check
     *
     * @param integer|null $id
     * @param string       $role
     *
     * @return User
     * @throws AuthenticationException
     */
    public static function findWithPermission($id = null, $role = 'admin')
    {
        if (empty($id)) {
            //Return the logged in user
            return Auth::user();
        }

        $requestedUser = self::findOrFail($id);
        if (Auth::user()->id == $requestedUser->id) {
            //The user they are after is themselves
            return $requestedUser;
        }

        //They are requesting a user that isn't them
        if (Auth::user()->hasRole($role)) {
            //They are an admin so that's alright
            return $requestedUser;
        }

        throw new AuthenticationException();
    }

    public function extendMembership($paymentMethod = null, DateTime $expiry = null)
    {
        if ($this->banned) {
            return;
        }
        
        if (empty($expiry)) {
            $expiry = Carbon::now()->addMonth();
        }
        $this->status = 'active';
        $this->active = true;
        if ($paymentMethod) {
            $this->payment_method = $paymentMethod;
        }
        $this->gift = '';
        $this->subscription_expires = $expiry;
        $this->save();
    }

    /**
     * @return array
     */
    public function getAuditFields()
    {
        return $this->auditFields;
    }

    public function isActive() {
        return $this->active && !$this->banned;
    }
}
