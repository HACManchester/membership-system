<?php

namespace BB\Http\Middleware;

use Illuminate\Http\Request;
use Fideloper\Proxy\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string
     */
    protected $proxies;

    /**
     * The current proxy header mappings.
     *
     * @var null|string|int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}