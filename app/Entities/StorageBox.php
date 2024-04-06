<?php namespace BB\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class StorageBox
 *
 * @property bool $active
 * @property integer $user_id
 * @package BB\Entities
 */
class StorageBox extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'storage_boxes';


    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'size',
        'active',
	    'location',
        'user_id'
    ];


    public function user()
    {
        return $this->belongsTo('\BB\Entities\User');
    }

    /**
     * Return a box record for the specified user
     * @param $userId
     * @return StorageBox|null
     */
    public static function findMember($userId)
    {
        return self::where('user_id', '=', $userId)->first();
    }

    public function getAvailableAttribute()
    {
        return ($this->active && ! $this->user_id);
    }

    public function isClaimed()
    {
        return $this->user_id > 0;
    }
} 
