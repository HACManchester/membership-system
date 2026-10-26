<?php namespace BB\Entities;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[UseFactory(RoleFactory::class)]
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    /**
     * Fillable fields
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'title', 'description', 'email_public', 'email_private', 'slack_channel'];

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public static function findByName($name) {
        $role = self::where('name', $name)->first();
        if ($role) {
            return $role;
        }
        throw new ModelNotFoundException();
    }

}