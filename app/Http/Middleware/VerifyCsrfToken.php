<?php

namespace BB\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery as Middleware;

/**
 * Laravel 13 renamed this middleware PreventRequestForgery. Besides the token,
 * it also accepts a same-origin Sec-Fetch-Site header.
 */
class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'gocardless/webhook',
    ];
}
