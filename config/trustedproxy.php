<?php

return [

    /*
    | The proxies whose X-Forwarded-* headers are believed (TrustProxies reads
    | this). Behind an HTTPS proxy, Laravel only knows a request was HTTPS, and
    | for which host, if it trusts the proxy that says so. Otherwise it builds
    | http:// URLs, which browsers block from https:// pages: Vite's module
    | scripts and Inertia's redirects among them.
    |
    | A comma-separated list of IPs or CIDR ranges, or '*' to trust whichever
    | address connects. Only use '*' when nothing but the proxy can reach the
    | app. Unset trusts no proxy.
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
