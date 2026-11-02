<?php namespace BB\Entities;

use BB\Observer\UserAuditObserver;
use Carbon\Carbon;
use Database\Factories\ProfileDataFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Uncast boolean columns read back from the database as 0/1, hence bool|int.
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $twitter
 * @property string|null $facebook
 * @property string|null $google_plus
 * @property string|null $github
 * @property string|null $irc
 * @property string|null $website
 * @property string|null $tagline
 * @property string|null $description
 * @property string|null $skills_array JSON, read and written through $skills
 * @property array $skills
 * @property bool|int $profile_photo
 * @property bool|int $profile_photo_private
 * @property bool|int $new_profile_photo
 * @property bool|int $profile_photo_on_wall
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[ObservedBy(UserAuditObserver::class)]
#[UseFactory(ProfileDataFactory::class)]
class ProfileData extends Model
{

    /** @use HasFactory<ProfileDataFactory> */
    use HasFactory;

    /**
     * profile_photo (the approval flag) and profile_photo_on_wall are admin-only,
     * so they're set explicitly rather than mass assigned.
     */
    protected $fillable = [
        'twitter', 'facebook', 'google_plus', 'github', 'irc', 'website', 'tagline', 'description', 'skills', 'profile_photo_private', 'new_profile_photo'
    ];

    protected $table = 'profile_data';

    protected $auditFields = array('profile_photo', 'profile_photo_on_wall');

    protected $attributes = [
        'profile_photo'         => 0,
        'profile_photo_private' => 0,
        'new_profile_photo'     => 0,
        'profile_photo_on_wall' => 0,
    ];

    /**
     * Stored as JSON in the skills_array column.
     *
     * @return Attribute<array, mixed>
     */
    protected function skills(): Attribute
    {
        return Attribute::make(
            get: fn () => (array)json_decode($this->skills_array ?? '[]'),
            set: fn ($skills) => ['skills_array' => json_encode(is_array($skills) ? $skills : [$skills])],
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array
     */
    public function getAuditFields()
    {
        return $this->auditFields;
    }
} 