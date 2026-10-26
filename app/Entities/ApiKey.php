<?php

namespace BB\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Database\Factories\ApiKeyFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[UseFactory(ApiKeyFactory::class)]
class ApiKey extends Model implements AuthenticatableContract
{
    /** @use HasFactory<ApiKeyFactory> */
    use HasFactory, SoftDeletes, Authenticatable;

    protected $fillable = ['api_token', 'description'];
}
