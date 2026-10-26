<?php

namespace BB\Entities;

use Carbon\Carbon;
use Database\Factories\SettingsFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $key_fob_id
 * @property integer $user_id
 * @property User    $user
 * @property string  $service
 * @property string  $response
 * @property bool    $delayed
 * @property Carbon  $created_at
 * @package BB\Entities
 */
#[UseFactory(SettingsFactory::class)]
class Settings extends Model
{
    /** @use HasFactory<SettingsFactory> */
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'settings';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['key', 'value'];

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
