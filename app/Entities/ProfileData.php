<?php namespace BB\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laracasts\Presenter\PresentableTrait;

/**
 * Class ProfileData
 *
 * @property bool $profile_photo
 * @property bool $new_profile_photo
 * @package BB\Entities
 */
class ProfileData extends Model
{

    use PresentableTrait;

    protected $fillable = [
        'twitter', 'facebook', 'google_plus', 'github', 'irc', 'website', 'tagline', 'description', 'skills', 'profile_photo', 'profile_photo_private', 'new_profile_photo', 'profile_photo_on_wall'
    ];

    protected $table = 'profile_data';

    protected $presenter = 'BB\Presenters\ProfileDataPresenter';

    protected $auditFields = array('profile_photo', 'profile_photo_on_wall');

    protected $attributes = [
        'profile_photo'         => 0,
        'profile_photo_private' => 0,
        'new_profile_photo'     => 0,
        'profile_photo_on_wall' => 0,
    ];

    public function setSkillsAttribute($skills)
    {
        if (is_array($skills)) {
            $this->attributes['skills_array'] = json_encode($skills);
        } else {
            $this->attributes['skills_array'] = json_encode([$skills]);
        }
    }


    public function getSkillsAttribute()
    {
        return (array)json_decode($this->skills_array ?? '[]');
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