<?php

namespace BB\Entities;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Induction
 *
 * @property bool $trained
 * @package BB\Entities
 */
class Induction extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'inductions';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array();

    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'user_id',
        'trained',
        'active',
        'is_trainer',
        'trainer_user_id'
    ];

    protected $attributes = [
        'active' => false,
        'is_trainer' => false,
    ];

    protected $casts = [
        'paid' => 'boolean',
        'active' => 'boolean',
        'is_trainer' => 'boolean',
    ];

    public function getDates()
    {
        return array('created_at', 'updated_at', 'trained');
    }


    public function user()
    {
        return $this->belongsTo('\BB\Entities\User');
    }

    public function trainerUser()
    {
        return $this->belongsTo('\BB\Entities\User');
    }

    public static function trainersFor($key)
    {
        return self::where('key', $key)->where('is_trainer', 1)->get();
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'key');
    }
}
