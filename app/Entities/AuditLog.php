<?php namespace BB\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'audit_log';


    /**
     * Fillable fields
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', 'admin_id', 'action', 'description'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }


} 