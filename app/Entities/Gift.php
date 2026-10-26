<?php namespace BB\Entities;

use Carbon\Carbon;
use Database\Factories\GiftFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(GiftFactory::class)]
class Gift extends Model
{
    /** @use HasFactory<GiftFactory> */
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gifts';

    /** The gifts table has no created_at or updated_at columns. */
    public $timestamps = false;


    /**
     * Fillable fields
     *
     * @var list<string>
     */
    protected $fillable = [
        'code', 'gifter_name', 'giftee_name', 'months', 'credit', 'expires'
    ];

} 