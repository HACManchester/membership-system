<?php

namespace BB\Entities;

use Carbon\Carbon;
use Database\Factories\TrainingRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $course_id
 * @property string $key legacy equipment key, superseded by course_id
 * @property bool $active
 * @property Carbon|null $trained
 * @property bool $is_trainer
 * @property int|null $trainer_user_id
 * @property Carbon|null $sign_off_requested_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[UseFactory(TrainingRecordFactory::class)]
class TrainingRecord extends Model
{
    /** @use HasFactory<TrainingRecordFactory> */
    use HasFactory;

    protected $table = 'inductions';

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

    protected function casts(): array
    {
        return [
            'paid' => 'boolean',
            'active' => 'boolean',
            'is_trainer' => 'boolean',
            'trained' => 'datetime',
            'sign_off_requested_at' => 'datetime',
        ];
    }

    #[Scope]
    protected function trained(Builder $query): void
    {
        $query->whereNotNull('trained');
    }


    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function trainerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_user_id');
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
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
