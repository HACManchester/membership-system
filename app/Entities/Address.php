<?php namespace BB\Entities;

use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Address
 *
 * @property integer $user_id
 * @property string $line_1
 * @property string $line_2
 * @property string $line_3
 * @property string $line_4
 * @property string $postcode
 * @property string $hash
 * @property bool $approved
 * @package BB\Address
 */
#[UseFactory(AddressFactory::class)]
class Address extends Model
{
    /** @use HasFactory<AddressFactory> */
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_address';


    /**
     * Fillable fields
     *
     * @var list<string>
     */
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