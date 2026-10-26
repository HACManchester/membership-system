<?php

namespace BB\Entities;

use BB\Exceptions\AuthenticationException;
use BB\Support\CurrentUser;
use BB\Traits\UserRoleTrait;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;
use BB\Entities\MaintainerGroup;
use Hash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
 * @property Carbon|null $subscription_expires
 * @property Carbon|null $banned_date
 * @property string $phone
 * @property integer $storage_box_payment_id
 * @property ProfileData|null $profile
 * @property string|null $secondary_payment_method
 * @property string|null $mandate_id
 * @property int $monthly_subscription
 * @property string|null $gocardless_setup_id
 * @property bool $postFob (false=collect, true=post)
 * @property \Carbon\Carbon|null $seen_at
 * @property-read \BB\Entities\Address|null $address
 * 
 * @package BB\Entities
 */
#[UseFactory(UserFactory::class)]
class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{

    /** @use HasFactory<UserFactory> */
    use HasFactory, UserRoleTrait, PresentableTrait, Authenticatable, Authorizable, CanResetPassword, Notifiable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var list<string>
     */
    protected $hidden = array('password', 'remember_token');

    protected $auditFields = array('induction_completed', 'trusted', 'key_holder');

    protected $presenter = 'BB\Presenters\UserPresenter';


    /**
     * Fillable fields
     *
     * @var list<string>
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
        'active' => 'boolean',
        // 'founder' => 'boolean',
        // 'director' => 'boolean',
        // 'trusted' => 'boolean',
        // 'key_holder' => 'boolean',
        // 'induction_completed' => 'boolean',
        // 'profile_private' => 'boolean',
        'banned' => 'boolean',
        'subscription_expires' => 'datetime',
        'banned_date' => 'datetime',
        'rules_agreed' => 'datetime',
        'seen_at' => 'datetime',
        'suspended_at' => 'datetime',
        'suppress_real_name' => 'boolean',
    ];




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

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('created_at', 'desc');
    }

    /**
     * @return HasMany<TrainingRecord, $this>
     */
    public function trainingRecords(): HasMany
    {
        return $this->hasMany(TrainingRecord::class);
    }

    public function keyFob()
    {
        return $this->hasMany(KeyFob::class)->where('active', true)->first();
    }

    /**
     * @return HasMany<KeyFob, $this>
     */
    public function keyFobs(): HasMany
    {
        return $this->hasMany(KeyFob::class)->where('active', true);
    }

    /**
     * @return HasOne<ProfileData, $this>
     */
    public function profile(): HasOne
    {
        return $this->hasOne(ProfileData::class);
    }

    /**
     * @return HasOne<Address, $this>
     */
    public function address(): HasOne
    {
        return $this->hasOne(Address::class)->orderBy('approved', 'asc');
    }

    /**
     * @return BelongsToMany<EquipmentArea, $this>
     */
    public function equipmentAreas(): BelongsToMany
    {
        return $this->belongsToMany(EquipmentArea::class);
    }

    
    /**
     * @return BelongsToMany<MaintainerGroup, $this>
     */
    public function maintainerGroups(): BelongsToMany
    {
        return $this->belongsToMany(MaintainerGroup::class);
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
        // TODO: Invert and pull from payment domain? Or drop it? Do we need to handle different payment methods?
        return in_array($this->attributes['payment_method'], ['gocardless-variable']);
    }

    /**
     * Is the user part of the admin group
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
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
        $profile = $this->profile;
        if (!$profile || (!$profile->profile_photo && !$profile->new_profile_photo)) {
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
        return !!$this->banned;
    }

    /**
     * @return bool
     */
    public function hasPaymentWarning()
    {
        return ($this->status == 'payment-warning');
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
        $this->suspended_at = Carbon::now();
        $this->save();
    }

    public function setPaymentWarning($gracePeriodDays = 10)
    {
        $this->status = 'payment-warning';
        $this->active = true; // Keep space access during warning period
        
        // Set subscription_expires to failure date + grace period for fair timing
        // TODO: Articles of association say 2 weeks from payment falling due
        $this->subscription_expires = Carbon::now()->addDays($gracePeriodDays);
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
        $this->seen_at = Carbon::now();
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
        //Return the logged in user
        if (empty($id)) {
            return CurrentUser::get();
        }

        $requestedUser = self::findOrFail($id);
        if (CurrentUser::get()->getAuthIdentifier() == $requestedUser->id) {
            //The user they are after is themselves
            return $requestedUser;
        }

        //They are requesting a user that isn't them
        if (CurrentUser::get()->hasRole($role)) {
            //They are an admin so that's alright
            return $requestedUser;
        }

        throw new AuthenticationException();
    }

    public function extendMembership($paymentMethod = null, ?DateTime $expiry = null)
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
        $this->subscription_expires = Carbon::instance($expiry);
        $this->suspended_at = null;
        $this->save();
    }

    /**
     * @return array
     */
    public function getAuditFields()
    {
        return $this->auditFields;
    }

    public function isActive()
    {
        return $this->active && !$this->banned;
    }
}
