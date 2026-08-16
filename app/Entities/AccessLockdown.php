<?php

namespace BB\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A period during which physical access to the space is shut down for general
 * membership. While one is running the keyfob export is filtered to members
 * holding one of this lockdown's roles, so the door system stops admitting
 * everyone else on its next poll.
 *
 * Lifted lockdowns are kept rather than deleted - the table is the incident log.
 *
 * @property int         $id
 * @property int         $started_by
 * @property User        $startedBy
 * @property string|null $reason
 * @property string[]    $roles
 * @property Carbon|null $lifted_at
 * @property int|null    $lifted_by
 * @property User|null   $liftedBy
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class AccessLockdown extends Model
{
    protected $fillable = ['started_by', 'reason', 'roles', 'lifted_by', 'lifted_at'];

    protected $casts = [
        'roles' => 'array',
        'lifted_at' => 'datetime',
    ];

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function liftedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lifted_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('lifted_at');
    }

    /**
     * The lockdown currently in force, if any.
     *
     * Only one should ever be running at a time - the controller won't start a
     * second - but taking the most recent means a stray duplicate degrades
     * gracefully instead of breaking the keyfob export.
     */
    public static function current(): ?self
    {
        return static::active()->latest('id')->first();
    }
}
