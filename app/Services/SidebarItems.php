<?php

namespace BB\Services;

use BB\Entities\User;
use BB\Entities\Course;
use BB\Support\CurrentUser;

class SidebarItems
{
    /**
     * The navigation items to show in the website's sidebar
     *
     * @return array
     */
    public function getItems()
    {
        $user = CurrentUser::find();

        if (!$user) {
            return [
                $this->anonymousNav(),
            ];
        }

        return [
            $this->accountNav($user),
            $this->resourcesNav($user),
            $this->adminNav($user),
        ];
    }

    protected function anonymousNav()
    {
        return [
            [
                'label' => '🔑 Login',
                'href' => route('login'),
                'active' => self::isActive('login')
            ],
            [
                'label' => '✔️ Become a Member',
                'href' => route('register'),
                'active' => self::isActive('register')
            ]
        ];
    }

    protected function accountNav(User $user)
    {
        return [
            [
                'label' => '🙂 Your Membership',
                'href' => route('account.show', $user->id),
                'active' => self::isActive('account.show', [$user->id]),
                'badge' => count($user->getAlerts()),
            ],
            [
                'label' => '💳 Manage Your Balance',
                'href' => route('account.balance.index', $user->id),
                'active' => self::isActive('account.balance.index', [$user->id])
            ],
            [
                'label' => 'ℹ️ General Induction',
                'href' => route('general-induction.show', $user->id),
                'active' => self::isActive('general-induction.show', [$user->id]),
                'highlight' => !$user->induction_completed
            ],
            [
                'label' => '💬 Forum',
                'href' => 'https://list.hacman.org.uk',
                'active' => false,
                'external' => true
            ],
        ];
    }

    protected function resourcesNav(User $user)
    {
        $resourcesItems = [
            [
                'label' => 'Members',
                'href' => route('members.index'),
                'active' => self::isActive('members.index')
            ],
            [
                'label' => 'Tools and Equipment',
                'href' => route('equipment.index'),
                'active' => self::isActive('equipment.index')
            ],
            [
                'label' => 'Inductions',
                'href' => route('courses.index'),
                'active' => self::isActive('courses.index'),
                'visible' => $user->can('viewAny', Course::class),
                'badge' => 'New'
            ],
            [
                'label' => 'Rooms',
                'href' => route('room.index'),
                'active' => self::isActive('room.index'),
                'visible' => $user->can('create', \BB\Entities\Room::class)
            ],
            [
                'label' => 'Area Coordinators',
                'href' => route('equipment_area.index'),
                'active' => self::isActive('equipment_area.index')
            ],
            [
                'label' => 'Maintainer Groups',
                'href' => route('maintainer_groups.index'),
                'active' => self::isActive('maintainer_groups.index')
            ]
        ];

        // array_values re-indexes after filtering: a filter that removes a middle
        // item leaves gaps in the keys, which json_encode would emit as an object
        // rather than an array — breaking the frontend's `.map` over the group.
        return array_values(array_filter($resourcesItems, function ($item) {
            return !isset($item['visible']) || $item['visible'];
        }));
    }

    protected function adminNav(User $user)
    {
        $adminItems = [
            [
                'label' => '👮 Admin',
                'href' => route('admin'),
                'active' => self::isActive('admin'),
                'visible' => $user->isAdmin()
            ],
            [
                'label' => '👮 Manage Members',
                'href' => route('account.index'),
                'active' => self::isActive('account.index'),
                'visible' => $user->isAdmin()
            ],
            [
                'label' => '👮 Log Files',
                'href' => route('logs'),
                'active' => self::isActive('logs'),
                'visible' => $user->isAdmin()
            ],
            [
                'label' => '💌 Newsletter',
                'href' => route('newsletter'),
                'active' => self::isActive('newsletter'),
                'visible' => $user->isAdmin()
            ],
            [
                'label' => '💰 Payments',
                'href' => route('payments.index'),
                'active' => self::isActive('payments.index'),
                'visible' => $user->hasRole('finance') || $user->isAdmin()
            ],
            [
                'label' => '🔒 Access Lockdown',
                'href' => route('access-lockdown.index'),
                'active' => self::isActive('access-lockdown.index'),
                'visible' => $user->isAdmin()
            ]
        ];

        return array_values(array_filter($adminItems, function ($item) {
            return $item['visible'];
        }));
    }

    /**
     * Check if the current route matches the given route name
     *
     * @param string $routeName
     * @param array $parameters
     * @return bool
     */
    private static function isActive($routeName, $parameters = [])
    {
        return url()->current() == route($routeName, $parameters);
    }
}
