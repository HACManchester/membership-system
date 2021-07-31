<?php namespace BB\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class AccessCode
 *
 * @property integer    $id
 * @property string     $name
 * @property string     $secret
 * @property integer    $counter
 * @property boolean    $enabled
 * @package BB\Entities
 */
class AccessCode extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'access_codes';

    protected $fillable = [
        'name',
        'code',
        'counter',
        'enabled'
    ];

    public function increment()
    {
        $this->counter = $this->counter + 1;
        $this->save();
    }

    public function enable()
    {
        $this->enabled   = true;
        $this->save();
    }

    public function disable()
    {
        $this->enabled   = false;
        $this->save();
    }


} 