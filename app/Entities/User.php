<?php

namespace BB\Entities;

use BB\Entities\Concerns\Audited;
use BB\Entities\Concerns\HasMembershipStatus;
use BB\Entities\Concerns\HasRoles;
use BB\Entities\Concerns\HasSubscription;
use BB\Entities\Presenters\UserPresenter;
use BB\Observer\UserAuditObserver;
use BB\Observer\UserObserver;
use BB\Support\MemberStatus;
use Carbon\Carbon;
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
 * @property MemberStatus $status
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
 * @property bool|null $online_only
 * @property bool|null $postFob false to collect the fob, true to have it posted
 * @property string|null $gift
 * @property Carbon|null $gift_expires
 * @property bool $newsletter
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
    use Audited, HasFactory, HasMembershipStatus, HasRoles, HasSubscription, UserPresenter, Authenticatable, Authorizable, CanResetPassword, Notifiable;

    protected $hidden = array('password', 'remember_token');

    /** @var list<string> */
    protected $auditFields = ['induction_completed', 'trusted', 'key_holder'];


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
        'status'                => MemberStatus::SettingUp->value,
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
            'email_verified' => 'boolean',
            'active' => 'boolean',
            'founder' => 'boolean',
            'trusted' => 'boolean',
            'key_holder' => 'boolean',
            'induction_completed' => 'boolean',
            'profile_private' => 'boolean',
            'banned' => 'boolean',
            'status' => MemberStatus::class,
            'subscription_expires' => 'datetime',
            'banned_date' => 'datetime',
            'rules_agreed' => 'datetime',
            'seen_at' => 'datetime',
            'suspended_at' => 'datetime',
            'gift_expires' => 'date',
            'cash_balance' => 'integer',
            'suppress_real_name' => 'boolean',
            'newsletter' => 'boolean',
            'online_only' => 'boolean',
            'postFob' => 'boolean',
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



    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

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
}
