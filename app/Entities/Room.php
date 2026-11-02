<?php

namespace BB\Entities;

use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A physical location in the space that equipment can be assigned to.
 * Admin-editable; replaces the hardcoded BB\Support\RoomOptions list.
 */
#[UseFactory(RoomFactory::class)]
class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'description'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Fall back to a slug derived from the name when one isn't supplied.
     *
     * @return Attribute<string, string|null>
     */
    protected function slug(): Attribute
    {
        return Attribute::set(fn ($value, array $attributes) => Str::slug($value ?: ($attributes['name'] ?? '')));
    }

    /**
     * @return HasMany<Equipment, $this>
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }
}
