<?php namespace BB\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gifts';


    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'code', 'gifter_name', 'giftee_name', 'months', 'credit', 'expires'
    ];

} 