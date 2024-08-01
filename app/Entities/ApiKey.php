<?php

namespace BB\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

class ApiKey extends Model implements AuthenticatableContract
{
    use SoftDeletes, Authenticatable;

    protected $fillable = ['api_token', 'description'];
}
