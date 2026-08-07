<?php namespace BB\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = ['name', 'title', 'description', 'email_public', 'email_private', 'slack_channel'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('\BB\Entities\User')->withTimestamps();
    }

    public static function findByName($name) {
        $role = self::where('name', $name)->first();
        if ($role) {
            return $role;
        }
        throw new ModelNotFoundException();
    }

}