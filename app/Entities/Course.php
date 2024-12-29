<?php

namespace BB\Entities;

use BB\Entities\Equipment;
use BB\Entities\Induction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;
    // TODO: Think about dependencies between courses? Too much complexity to build in (for now)?

    protected $fillable = [
        'name',
        'slug',
        'description',
        'format', // group, quiz, one-on-one
        'format_description',
        'frequency', // self-serve (quiz), regular, ad-hoc
        'frequency_description',
        'wait_time', // Free-text, but conventional format. "1-2 weeks"
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'induction_category', 'slug');
    }

    public function inductions()
    {
        return $this->hasMany(Induction::class, 'key', 'slug');
    }
}
