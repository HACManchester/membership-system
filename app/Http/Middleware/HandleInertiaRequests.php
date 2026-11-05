<?php

namespace BB\Http\Middleware;

use BB\Services\SidebarItems;
use BB\Support\CurrentUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Session\Session;
use Inertia\Inertia;
use Inertia\Middleware;
use Inertia\Support\Header;
use Inertia\Support\SessionKey;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * An Inertia visit is an XHR, which can't follow a redirect to another site
     * (GoCardless's payment pages, say): turn one into Inertia::location(), which
     * makes the browser go there itself. Other requests, prefetches (as Inertia
     * does for redirects with a fragment), and redirects within the app are left
     * alone, so a controller can just `redirect()->away()`.
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        $this->flashNotificationToPage($request);

        $response = parent::handle($request, $next);

        if ($request->header(Header::INERTIA)
            && ! $request->prefetch()
            && $response instanceof RedirectResponse
            && $this->isExternal($request, $response->getTargetUrl())) {
            return Inertia::location($response->getTargetUrl());
        }

        return $response;
    }

    /**
     * Another host, or the same host on another port. The scheme isn't compared:
     * behind the TLS-terminating proxy the app may see http for its own https URLs.
     */
    private function isExternal(Request $request, string $url): bool
    {
        $target = parse_url($url);

        if (! isset($target['host'])) {
            return false;
        }

        if (strcasecmp($target['host'], $request->getHost()) !== 0) {
            return true;
        }

        return isset($target['port']) && $target['port'] !== $request->getPort();
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => function () {
                $user = CurrentUser::find();

                return $user ? [
                    'user' => [
                        'name' => $user->name,
                        'account_path' => route('account.show', $user),
                        // 'id' => $user->id,
                        // 'email' => $user->email,
                        // 'status' => $user->status,
                        // 'isAdmin' => $user->isAdmin(),
                        // 'profile_photo' => $user->profile ? $user->profile->profile_photo : null,
                        // 'roles' => $user->roles->pluck('name'),
                    ],
                ] : null;
            },
            'navRoutes' => function () {
                return (new SidebarItems)->getItems();
            },
        ]);
    }

    /**
     * Hands the message flashed to this request to Inertia's flash data as
     * `notification`, which FlashSnackbar shows. Controllers flash it with
     * `->with('success'|'error', ...)` or with the legacy FlashNotification facade,
     * which Blade pages read. Unlike a prop, Inertia's flash data isn't kept in the
     * browser's history or carried into partial reloads, so a dismissed message
     * doesn't come back. It's set for this request only: a Blade page ignores it,
     * and Inertia keeps it across a redirect.
     */
    private function flashNotificationToPage(Request $request): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $notification = $this->flashedNotification($request->session());

        if ($notification !== null) {
            $request->session()->now(SessionKey::FLASH_DATA, [
                ...Inertia::getFlashed($request),
                'notification' => $notification,
            ]);
        }
    }

    /**
     * @return array{message: string, severity: 'success'|'info'|'warning'|'error'}|null
     */
    private function flashedNotification(Session $session): ?array
    {
        if (is_string($error = $session->get('error'))) {
            return ['message' => $error, 'severity' => 'error'];
        }

        if (is_string($success = $session->get('success'))) {
            return ['message' => $success, 'severity' => 'success'];
        }

        $message = $session->get('flash_notification.message');

        if (! is_string($message)) {
            return null;
        }

        $severity = match ($session->get('flash_notification.level')) {
            'danger' => 'error',
            'warning' => 'warning',
            'success' => 'success',
            default => 'info',
        };

        return ['message' => $message, 'severity' => $severity];
    }
}
