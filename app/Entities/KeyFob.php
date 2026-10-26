<?php namespace BB\Entities;

use Database\Factories\KeyFobFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class KeyFob
 *
 * @property integer $id
 * @property bool    $lost
 * @property bool    $active
 * @property User    $user
 * @package BB\Entities
 */
#[UseFactory(KeyFobFactory::class)]
class KeyFob extends Model
{
    /** @use HasFactory<KeyFobFactory> */
    use HasFactory;


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'key_fobs';

    protected $fillable = [
        'user_id',
        'key_id'
    ];

    protected $attributes = [
        'active' => 1,
        'lost'   => 0,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markLost()
    {
        $this->lost   = true;
        $this->active = false;
        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->whereActive(true);
    }
} 