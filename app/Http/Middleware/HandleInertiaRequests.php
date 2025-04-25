<?php

namespace BB\Http\Middleware;

use BB\Services\SidebarItems;
use Illuminate\Http\Request;
use Inertia\Middleware;

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
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => function () use ($request) {
                $user = $request->user();

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
            }
        ]);
    }
}
