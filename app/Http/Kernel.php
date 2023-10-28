<?php

namespace BB\Http;

use BB\Http\Middleware\ACSAuthentication;
use BB\Http\Middleware\BrandingHeaders;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \BB\Http\Middleware\ACSSessionControl::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \BB\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \BB\Http\Middleware\SSLOnly::class,
            \BB\Http\Middleware\BrandingHeaders::class,
            \BB\Http\Middleware\HttpsProtocol::class,
            \BB\Http\Middleware\UserSeen::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'          => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic'    => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings'      => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can'           => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'         => \BB\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle'      => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'role'          => \BB\Http\Middleware\HasRole::class,
        'trusted'       => \BB\Http\Middleware\IsTrusted::class,
        'acs'           => \BB\Http\Middleware\ACSAuthentication::class,
    ];
}
