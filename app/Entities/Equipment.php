<?php

namespace BB\Entities;

use BB\Entities\Course;
use BB\Scopes\OrderScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracasts\Presenter\PresentableTrait;

/**
 * Class Equipment
 *
 * @property array   $photos
 * @property string  $slug
 * @property array   $ppe
 * @property Carbon  $obtained_at
 * @property Carbon  $removed_at
 * @property integer $usageCost
 * @property string  $induction_category
 * @property \Illuminate\Database\Eloquent\Collection<\BB\Entities\Course> $courses
 * @package BB\Entities
 */
class Equipment extends Model
{
    use PresentableTrait, SoftDeletes;

    protected $presenter = 'BB\Presenters\EquipmentPresenter';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'manufacturer',
        'model_number',
        'serial_number',
        'colour',
        'room',
        'detail',
        'slug',
        'description',
        'help_text',
        'maintainer_group_id',
        'requires_induction',
        'induction_category',
        'working',
        'permaloan',
        'permaloan_user_id',
        'access_fee',
        'photos',
        'archive',
        'obtained_at',
        'removed_at',
        'asset_tag_id',
        'usage_cost',
        'usage_cost_per',
        'ppe',
        'dangerous',
        'induction_instructions',
        'trainer_instructions',
        'trained_instructions',
        'docs',
        'access_code',
        'accepting_inductions'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at'
    ];

    protected static function boot()
    {
        parent::boot();

        // TODO: Would be nice to support multiple orderings, i.e. order by "Working: yes/no" primarily, name secondarily
        static::addGlobalScope(new OrderScope('name', 'asc'));
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getDates()
    {
        return array('created_at', 'updated_at', 'obtained_at', 'removed_at');
    }

    public function role()
    {
        return $this->belongsTo('\BB\Entities\Role', 'managing_role_id');
    }

    public function maintainerGroup(): BelongsTo
    {
        return $this->belongsTo(MaintainerGroup::class);
    }

    /**
     * Does the equipment need an induction to use it
     *
     * @return bool
     */
    public function requiresInduction()
    {
        return (bool)$this->requires_induction;
    }

    public function hasUsageCharge()
    {
        return (bool)$this->usageCost;
    }

    /**
     * @return bool
     */
    public function isWorking()
    {
        return (bool)$this->working;
    }

    /**
     * @return bool
     */
    public function hasPhoto()
    {
        return (bool)count($this->photos);
    }

    /**
     * @return bool
     */
    public function isPermaloan()
    {
        return (bool)$this->permaloan;
    }

    public function isDangerous()
    {
        return (bool)$this->dangerous;
    }

    public function isManagedByGroup()
    {
        return (bool)$this->managing_role_id;
    }

    /**
     * Generate the filename for the image, this will depend on which in the sequence it is
     *
     * @param int $num
     * @return string
     */
    public function getPhotoPath($num = 0)
    {
        return $this->getPhotoBasePath() . $this->photos[$num]['path'];
    }

    /**
     * Get the base path all the equipment images live under
     *
     * @return string
     */
    public function getPhotoBasePath()
    {
        return 'equipment-images/';
    }

    /**
     * Add a photo name to the photos array
     *
     * @param $fileName
     */
    public function addPhoto($fileName)
    {
        $photos = $this->photos;
        array_push($photos, ['path' => $fileName]);
        $this->photos = $photos;
        $this->save();
    }

    public function removePhoto($id)
    {
        $photos = $this->photos;
        unset($photos[$id]);
        $this->photos = array_values($photos);
        $this->save();
    }

    /**
     * Get the full url to a product image
     *
     * @param int $num
     * @return string
     */
    public function getPhotoUrl($num = 1)
    {
        return asset('storage/' . $this->getPhotoPath($num));
    }

    public function getNumPhotos()
    {
        return count($this->photos);
    }

    public function setPhotosAttribute(array $value)
    {
        if (empty($value)) {
            $value = [];
        }
        $this->attributes['photos'] = json_encode($value);
    }

    /**
     * @return array
     */
    public function getPhotosAttribute()
    {
        if (empty($this->attributes['photos'])) {
            return [];
        }
        $photos = json_decode($this->attributes['photos'], true);
        if ($photos === null) {
            return [];
        }
        return $photos;
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = strtolower($value);
    }

    public function getObtainedAtAttribute()
    {
        if (!$this->attributes['obtained_at'] || $this->attributes['obtained_at'] == '0000-00-00') {
            return null;
        }
        return new Carbon($this->attributes['obtained_at']);
    }

    public function getRemovedAtAttribute()
    {
        if (!$this->attributes['removed_at'] || $this->attributes['removed_at'] == '0000-00-00') {
            return null;
        }
        return new Carbon($this->attributes['removed_at']);
    }

    public function getUsageCostAttribute()
    {
        return $this->attributes['usage_cost'] / 100;
    }

    public function setUsageCostAttribute($value)
    {
        $this->attributes['usage_cost'] = floatval($value) * 100;
    }

    /**
     * @return array
     */
    public function getPpeAttribute()
    {
        $items = json_decode($this->attributes['ppe'], true);
        if (is_array($items)) {
            // Filter out empty strings and trim whitespace
            return array_values(array_filter($items, function($item) {
                return !empty(trim($item));
            }));
        } else {
            return [];
        }
    }

    /**
     * @param string $value
     */
    public function setPpeAttribute($value)
    {
        $this->attributes['ppe'] = json_encode($value);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_equipment')
            ->withTimestamps();
    }
}
