<?php

namespace BB\Entities;

use Carbon\Carbon;
use Database\Factories\SettingsFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A site-wide value, such as the general induction code, keyed by name.
 *
 * @property string      $key
 * @property string      $value
 * @property int|null    $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $updatedBy
 */
#[UseFactory(SettingsFactory::class)]
class Settings extends Model
{
    /** @use HasFactory<SettingsFactory> */
    use HasFactory;

    protected $table = 'settings';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function put($key, $value)
    {
        self::create(['key' => $key, 'value' => $value]);
    }

    public static function get(string $key)
    {
        $setting = self::findOrFail($key);
        return $setting->value;
    }

    public static function change(string $key, $value)
    {
        $setting = self::findOrFail($key);
        $setting->value = $value;
        $setting->save();
    }
}
