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
 * @property int $id
 * @property int $user_id
 * @property string $key_id
 * @property bool $active
 * @property bool $lost
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

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'lost' => 'boolean',
        ];
    }

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