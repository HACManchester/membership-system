<?php

namespace BB\Entities;

use BB\Entities\Equipment;
use BB\Entities\Induction;
use BB\Entities\Settings;
use BB\Presenters\CoursePresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracasts\Presenter\PresentableTrait;

class Course extends Model
{
    use SoftDeletes, PresentableTrait;

    protected $presenter = CoursePresenter::class;

    // TODO: Think about dependencies between courses? Too much complexity to build in (for now)?

    protected $fillable = [
        'name',
        'slug',
        'description',
        'format', // group, quiz, one-on-one
        'format_description',
        'frequency', // self-serve (quiz), regular, ad-hoc
        'frequency_description',
        'wait_time', // Free-text, but conventional format. "1-2 weeks"
        'paused_at',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function equipment()
    {
        return $this->belongsToMany(Equipment::class, 'course_equipment')
            ->withTimestamps();
    }

    public function inductions()
    {
        return $this->hasMany(Induction::class, 'key', 'slug');
    }

    public static function formatOptions()
    {
        return collect([
            'group' => 'Group class',
            'quiz' => 'Online Quiz',
            'one-on-one' => 'One-on-one',
        ]);
    }

    public static function frequencyOptions()
    {
        return collect([
            'self-serve' => 'Self-serve',
            'regular' => 'Regular',
            'ad-hoc' => 'On-demand',
        ]);
    }

    /**
     * Check if the inductions feature is live for everyone
     *
     * @return bool
     */
    public static function isLive()
    {
        try {
            $inductionsLive = Settings::get('inductions_live');
            return $inductionsLive === 'true';
        } catch (\Exception $e) {
            // If setting doesn't exist, feature is in preview mode
            return false;
        }
    }

    /**
     * Check if the inductions feature is in preview mode
     *
     * @return bool
     */
    public static function isPreview()
    {
        return !static::isLive();
    }

    /**
     * Check if the course is currently paused
     *
     * @return bool
     */
    public function isPaused()
    {
        return !is_null($this->paused_at);
    }
}
