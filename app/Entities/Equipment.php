<?php

namespace BB\Entities;

use BB\Scopes\OrderScope;
use Carbon\Carbon;
use Database\Factories\EquipmentFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string|null $manufacturer
 * @property string|null $model_number
 * @property string|null $serial_number
 * @property string|null $colour
 * @property string|null $room legacy free-text room, superseded by room_id
 * @property int|null $room_id
 * @property string|null $detail
 * @property string|null $description
 * @property string|null $help_text
 * @property string|null $admin_notes
 * @property array $ppe
 * @property array $photos
 * @property string|null $docs
 * @property int|null $managing_role_id
 * @property int|null $maintainer_group_id
 * @property bool $requires_induction
 * @property bool|null $accepting_inductions
 * @property string|null $induction_category
 * @property string|null $induction_instructions
 * @property string|null $trainer_instructions
 * @property string|null $trained_instructions
 * @property bool $working
 * @property bool $permaloan
 * @property int|null $permaloan_user_id
 * @property bool|null $dangerous
 * @property bool $lone_working
 * @property int $access_fee
 * @property float|int $usage_cost in pounds; stored in pence
 * @property-read float|int $usageCost
 * @property string|null $usage_cost_per
 * @property bool $archive
 * @property string|null $asset_tag_id
 * @property string|null $access_code
 * @property Carbon|null $obtained_at
 * @property Carbon|null $removed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[UseFactory(EquipmentFactory::class)]
class Equipment extends Model
{
    /** @use HasFactory<EquipmentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'manufacturer',
        'model_number',
        'serial_number',
        'colour',
        'room_id',
        'admin_notes',
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
        'accepting_inductions',
        'lone_working'
    ];

    protected function casts(): array
    {
        return [
            'working' => 'boolean',
            'permaloan' => 'boolean',
            'dangerous' => 'boolean',
            'lone_working' => 'boolean',
            'archive' => 'boolean',
            'requires_induction' => 'boolean',
            'accepting_inductions' => 'boolean',
            'obtained_at' => 'datetime',
            'removed_at' => 'datetime',

            // Swap these over later
            // 'photos' => 'array',
            // 'ppe' => 'array',
            // 'docs' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        // TODO: Would be nice to support multiple orderings, i.e. order by "Working: yes/no" primarily, name secondarily
        static::addGlobalScope(new OrderScope('name', 'asc'));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }


    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'managing_role_id');
    }

    /**
     * @return BelongsTo<MaintainerGroup, $this>
     */
    public function maintainerGroup(): BelongsTo
    {
        return $this->belongsTo(MaintainerGroup::class);
    }

    /**
     * The room this equipment lives in.
     *
     * Named `roomModel` rather than `room` because the legacy `room` string
     * column still exists and would shadow a relation of the same name. Rename
     * to `room()` once that column is dropped (Phase 5).
     * @return BelongsTo<Room, $this>
     */
    public function roomModel(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function permaloanUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'permaloan_user_id');
    }

    /**
     * Does the equipment need an induction to use it
     */
    public function requiresInduction(): bool
    {
        return (bool)$this->requires_induction;
    }

    public function hasUsageCharge(): bool
    {
        return (bool)$this->usageCost;
    }

    public function hasPhoto(): bool
    {
        return (bool)count($this->photos);
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

    /**
     * @return Attribute<array, array>
     */
    protected function photos(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (empty($value)) {
                    return [];
                }
                $photos = json_decode($value, true);
                if ($photos === null) {
                    return [];
                }
                return $photos;
            },
            set: function (array $value) {
                if (empty($value)) {
                    $value = [];
                }
                return json_encode($value);
            },
        );
    }

    /**
     * @return Attribute<string|null, string>
     */
    protected function slug(): Attribute
    {
        return Attribute::set(fn ($value) => strtolower($value));
    }

    /**
     * Read and written in pounds, stored in pence. Also readable as `usageCost`,
     * hence reading from $attributes rather than the value for the key asked for.
     *
     * @return Attribute<float|int, mixed>
     */
    protected function usageCost(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $attributes['usage_cost'] / 100,
            set: fn ($value) => floatval($value) * 100,
        );
    }

    /**
     * @return Attribute<array, mixed>
     */
    protected function ppe(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $items = json_decode($value ?? '', true);
                if (is_array($items)) {
                    // Filter out empty strings and trim whitespace
                    return array_values(array_filter($items, function($item) {
                        return !empty(trim($item));
                    }));
                } else {
                    return [];
                }
            },
            set: fn ($value) => json_encode($value),
        );
    }

    /**
     * @return BelongsToMany<Course, $this>
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_equipment')
            ->withTimestamps();
    }
}
