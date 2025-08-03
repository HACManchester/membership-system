<?php

namespace BB\Entities;

use BB\Entities\Equipment;
use BB\Entities\Induction;
use BB\Presenters\CoursePresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracasts\Presenter\PresentableTrait;

/**
 * @property \Illuminate\Database\Eloquent\Collection<\BB\Entities\Equipment> $equipment
 * @property \Illuminate\Database\Eloquent\Collection<\BB\Entities\Induction> $inductions
 */
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
        'training_organisation_description',
        'schedule_url',
        'quiz_url',
        'request_induction_url',
        'paused_at',
        'live',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'live' => 'boolean',
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
        return $this->hasMany(Induction::class);
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
     * Check if the course is currently paused
     *
     * @return bool
     */
    public function isPaused()
    {
        return !is_null($this->paused_at);
    }
}
