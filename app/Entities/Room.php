<?php

namespace BB\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A physical location in the space that equipment can be assigned to.
 * Admin-editable; replaces the hardcoded BB\Support\RoomOptions list.
 */
class Room extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description'];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Fall back to a slug derived from the name when one isn't supplied.
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = Str::slug($value ?: ($this->attributes['name'] ?? ''));
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }
}
