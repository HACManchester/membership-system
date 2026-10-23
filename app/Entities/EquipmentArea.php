<?php

namespace BB\Entities;

use BB\Entities\MaintainerGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentArea extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description'];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function areaCoordinators(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return HasMany<MaintainerGroup, $this>
     */
    public function maintainerGroups(): HasMany
    {
        return $this->hasMany(MaintainerGroup::class);
    }
}
