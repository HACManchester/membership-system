<?php

namespace BB\Entities;

use BB\Support\CourseFormat;
use BB\Support\CourseFrequency;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Illuminate\Database\Eloquent\Collection<int, \BB\Entities\Equipment> $equipment
 * @property \Illuminate\Database\Eloquent\Collection<int, \BB\Entities\TrainingRecord> $trainingRecords
 * @property \Carbon\Carbon|null $paused_at
 * @property CourseFormat $format
 * @property CourseFrequency $frequency
 * @property-read string $format_label
 * @property-read string $frequency_label
 */
#[UseFactory(CourseFactory::class)]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory, SoftDeletes;

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

    protected function casts(): array
    {
        return [
            'live' => 'boolean',
            'paused_at' => 'datetime',
            'format' => CourseFormat::class,
            'frequency' => CourseFrequency::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsToMany<Equipment, $this>
     */
    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'course_equipment')
            ->withTimestamps();
    }

    /**
     * @return HasMany<TrainingRecord, $this>
     */
    public function trainingRecords(): HasMany
    {
        return $this->hasMany(TrainingRecord::class);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function formatLabel(): Attribute
    {
        return Attribute::get(fn () => $this->format->label());
    }

    /**
     * @return Attribute<string, never>
     */
    protected function frequencyLabel(): Attribute
    {
        return Attribute::get(fn () => $this->frequency->label());
    }

    /**
     * Check if the course is currently paused
     */
    public function isPaused(): bool
    {
        return !is_null($this->paused_at);
    }
}
