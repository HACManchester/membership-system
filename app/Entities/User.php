<?php

namespace BB\Entities;

use BB\Entities\Presenters\UserPresenter;
use BB\Exceptions\AuthenticationException;
use BB\Observer\UserAuditObserver;
use BB\Observer\UserObserver;
use BB\Support\CurrentUser;
use BB\Traits\UserRoleTrait;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Uncast boolean columns read back from the database as 0/1, hence bool|int.
 *
 * @property int $id
 * @property string|null $hash
 * @property string $given_name
 * @property string $family_name
 * @property string|null $display_name
 * @property string|null $announce_name
 * @property string|null $pronouns
 * @property bool $suppress_real_name
 * @property string|null $import_match_string
 * @property string $email
 * @property bool $email_verified
 * @property string|null $secondary_email
 * @property string|null $password
 * @property string|null $remember_token
 * @property string $phone
 * @property string|null $emergency_contact
 * @property string|null $notes
 * @property bool $active
 * @property bool $founder
 * @property string $status
 * @property bool $trusted
 * @property bool $key_holder
 * @property int|null $key_deposit_payment_id
 * @property int|null $storage_box_payment_id
 * @property bool $induction_completed
 * @property int|null $inducted_by
 * @property string|null $payment_method
 * @property string|null $secondary_payment_method
 * @property int $payment_day
 * @property int|null $monthly_subscription in pounds
 * @property string|null $subscription_id
 * @property string|null $mandate_id
 * @property string|null $gocardless_setup_id
 * @property string|null $last_subscription_payment Y-m-d
 * @property Carbon|null $subscription_expires
 * @property Carbon|null $suspended_at
 * @property int $cash_balance in pence
 * @property bool $profile_private
 * @property bool $banned
 * @property string|null $banned_reason
 * @property Carbon|null $banned_date
 * @property Carbon|null $rules_agreed
 * @property Carbon|null $seen_at
 * @property string|null $find_us
 * @property bool|int|null $online_only
 * @property bool|int|null $postFob false to collect the fob, true to have it posted
 * @property string|null $gift
 * @property string|null $gift_expires Y-m-d
 * @property bool|int $newsletter
 * @property bool $visited_forum
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read string|null $name the display name
 */
#[ObservedBy([UserObserver::class, UserAuditObserver::class])]
#[UseFactory(UserFactory::class)]
class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{

    /** @use HasFactory<UserFactory> */
    use HasFactory, UserRoleTrait, UserPresenter, Authenticatable, Authorizable, CanResetPassword, Notifiable;

    protected $hidden = array('password', 'remember_token');

    protected $auditFields = array('induction_completed', 'trusted', 'key_holder');


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
        'rules_agreed',
        'postFob',
        'pronouns',
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

    protected function casts(): array
    {
        return [
            'visited_forum' => 'boolean',

            // TODO: Enable these casts one at a time and verify casting will not break references
            'email_verified' => 'boolean',
            'active' => 'boolean',
            'founder' => 'boolean',
            'trusted' => 'boolean',
            'key_holder' => 'boolean',
            'induction_completed' => 'boolean',
            'profile_private' => 'boolean',
            'banned' => 'boolean',
            'subscription_expires' => 'datetime',
            'banned_date' => 'datetime',
            'rules_agreed' => 'datetime',
            'seen_at' => 'datetime',
            'suspended_at' => 'datetime',
            'suppress_real_name' => 'boolean',
            // Hashes plain text but keeps an existing hash, so Laravel's rehash on login isn't hashed twice
            'password' => 'hashed',
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

    /**
     * @return Attribute<string|null, never>
     */
    protected function name(): Attribute
    {
        return Attribute::get(fn ($value, array $attributes) => $attributes['display_name']);
    }

    /**
     * Days after the 28th become the 1st, so the payment day exists in every month.
     *
     * @return Attribute<int, int>
     */
    protected function paymentDay(): Attribute
    {
        return Attribute::set(fn ($value) => $value > 28 ? 1 : $value);
    }

    /**
     * Can the user see protected member photos?
     * Only available to active members
     */
    public function shouldMemberSeeProtectedPhoto(): bool
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
     */
    public function canMemberChangeSubAmount(): bool
    {
        // TODO: Invert and pull from payment domain? Or drop it? Do we need to handle different payment methods?
        return in_array($this->attributes['payment_method'], ['gocardless-variable']);
    }

    /**
     * Is the user part of the admin group
     */
    public function isAdmin(): bool
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

    public function isBanned(): bool
    {
        return !!$this->banned;
    }

    public function hasPaymentWarning(): bool
    {
        return ($this->status == 'payment-warning');
    }
    
    public function isSuspended(): bool
    {
        return ($this->status == 'suspended');
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where(['active' => true, 'banned' => false]);
    }

    #[Scope]
    protected function notSpecialCase(Builder $query): void
    {
        $query->where('status', '!=', 'honorary')
            ->where(function ($subquery) {
                $subquery->whereNull('gift_expires')->orWhere('gift_expires', '<=', Carbon::today());
            });
    }

    #[Scope]
    protected function leaving(Builder $query): void
    {
        $query->where('status', '=', 'leaving');
    }

    #[Scope]
    protected function paymentWarning(Builder $query): void
    {
        $query->where('status', '=', 'payment-warning');
    }

    #[Scope]
    protected function suspended(Builder $query): void
    {
        $query->where('status', '=', 'suspended');
    }

    #[Scope]
    protected function recentlyLapsed(Builder $query): void
    {
        $query->whereActive(false)
            ->where('banned', false)
            ->where('subscription_expires', '>=', Carbon::today()->subMonths(6));
    }

    #[Scope]
    protected function newsletterOptIns(Builder $query): void
    {
        $query->where('newsletter', '=', true);
    }


    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

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

    public function isActive(): bool
    {
        return $this->active && !$this->banned;
    }
}
