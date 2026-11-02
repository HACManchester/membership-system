<?php namespace BB\Entities;

use Database\Factories\GiftFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

} 