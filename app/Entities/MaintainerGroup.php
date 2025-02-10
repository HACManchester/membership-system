<?php

namespace BB\Entities;

use BB\Entities\EquipmentArea;
use BB\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintainerGroup extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'equipment_area_id'];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function maintainers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function equipmentArea(): BelongsTo
    {
        return $this->belongsTo(EquipmentArea::class);
    }
}
