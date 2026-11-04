<?php namespace BB\Entities;

use Carbon\Carbon;
use Database\Factories\GiftFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string|null $gifter_name
 * @property string|null $giftee_name
 * @property int $months
 * @property float $credit
 * @property Carbon $expires
 */
#[UseFactory(GiftFactory::class)]
class Gift extends Model
{
    /** @use HasFactory<GiftFactory> */
    use HasFactory;

    /** The gifts table has no created_at or updated_at columns. */
    public $timestamps = false;


    protected $fillable = [
        'code', 'gifter_name', 'giftee_name', 'months', 'credit', 'expires'
    ];

    protected function casts(): array
    {
        return [
            'months' => 'integer',
            'credit' => 'float',
            'expires' => 'date',
        ];
    }

} 