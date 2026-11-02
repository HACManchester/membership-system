<?php namespace BB\Entities;

use Carbon\Carbon;
use Database\Factories\KeyFobFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Uncast boolean columns read back from the database as 0/1, hence bool|int.
 *
 * @property int $id
 * @property int $user_id
 * @property string $key_id
 * @property bool|int $active
 * @property bool|int $lost
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[UseFactory(KeyFobFactory::class)]
class KeyFob extends Model
{
    /** @use HasFactory<KeyFobFactory> */
    use HasFactory;

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

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereActive(true);
    }
} 