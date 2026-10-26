<?php

namespace BB\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * Only the scheme is taken from the proxy. Both proxies (nginx in production,
     * OrbStack locally) pass the real Host through, and production's Apache
     * resolves the client IP itself with mod_remoteip. The proxy also passes
     * client-sent X-Forwarded-Host through untouched, so trusting it would let
     * anyone choose the host in password-reset links.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_PROTO;
}