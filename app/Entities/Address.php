<?php namespace BB\Entities;

use Carbon\Carbon;
use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $line_1
 * @property string|null $line_2
 * @property string|null $line_3
 * @property string|null $line_4
 * @property string|null $postcode
 * @property string|null $hash
 * @property bool|int|null $approved read back from the database as 0/1
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[UseFactory(AddressFactory::class)]
class Address extends Model
{
    /** @use HasFactory<AddressFactory> */
    use HasFactory;

    protected $table = 'user_address';


    protected $fillable = [
        'user_id', 'line_1', 'line_2', 'line_3', 'line_4', 'postcode', 'hash'
    ];

    protected $attributes = [
        'approved' => 0,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 