<?php

namespace BB\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function areaCoordinators(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
