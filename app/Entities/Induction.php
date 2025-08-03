<?php

namespace BB\Entities;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Induction
 *
 * @property bool $trained
 * @property int|null $course_id
 * @property Course|null $course
 * @property User $user
 * @property User|null $trainerUser
 * @property \Carbon\Carbon|null $sign_off_requested_at
 * @package BB\Entities
 */
class Induction extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'inductions';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array();

    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'user_id',
        'trained',
        'active',
        'is_trainer',
        'trainer_user_id',
        'course_id',
        'sign_off_requested_at'
    ];

    protected $attributes = [
        'active' => false,
        'is_trainer' => false,
    ];

    protected $casts = [
        'paid' => 'boolean',
        'active' => 'boolean',
        'is_trainer' => 'boolean',
        'trained' => 'datetime',
        'sign_off_requested_at' => 'datetime',
    ];

    public function scopeTrained($query)
    {
        return $query->whereNotNull('trained');
    }


    public function user()
    {
        return $this->belongsTo('\BB\Entities\User');
    }

    public function trainerUser()
    {
        return $this->belongsTo('\BB\Entities\User', 'trainer_user_id');
    }

    public static function trainersFor($key)
    {
        return self::where('key', $key)->where('is_trainer', true)->get();
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Sign-off request expiration time in hours
     */
    const SIGN_OFF_EXPIRATION_HOURS = 7 * 24;

    /**
     * Check if a sign-off request has expired
     */
    public function isSignOffExpired(): bool
    {
        if (!$this->sign_off_requested_at) {
            return false;
        }

        return $this->sign_off_requested_at->lt(now()->subHours(self::SIGN_OFF_EXPIRATION_HOURS));
    }

    /**
     * Get the expiry date for the sign-off request
     */
    public function getSignOffExpiryDate(): ?string
    {
        if (!$this->sign_off_requested_at) {
            return null;
        }

        return $this->sign_off_requested_at
            ->copy()
            ->addHours(self::SIGN_OFF_EXPIRATION_HOURS)
            ->toISOString();
    }
}
