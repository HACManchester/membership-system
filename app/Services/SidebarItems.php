<?php

namespace BB\Services;

use BB\Entities\User;
use Illuminate\Support\Facades\Auth;

class SidebarItems
{
    /** @var User|null */
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * The navigation items to show in the website's sidebar
     *
     * @return array
     */
    public function getItems()
    {
        if (!$this->user) {
            return [
                $this->anonymousNav(),
            ];
        }

        return [
            $this->accountNav(),
            $this->resourcesNav(),
            $this->adminNav(),
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

    protected function accountNav()
    {
        return [
            [
                'label' => '🙂 Your Membership',
                'href' => route('account.show', $this->user->id),
                'active' => self::isActive('account.show', [$this->user->id]),
                'badge' => count($this->user->getAlerts()),
            ],
            [
                'label' => '💳 Manage Your Balance',
                'href' => route('account.balance.index', $this->user->id),
                'active' => self::isActive('account.balance.index', [$this->user->id])
            ],
            [
                'label' => 'ℹ️ General Induction',
                'href' => route('general-induction.show', $this->user->id),
                'active' => self::isActive('general-induction.show', [$this->user->id]),
                'highlight' => !$this->user->induction_completed
            ],
            [
                'label' => '💬 Forum',
                'href' => 'https://list.hacman.org.uk',
                'active' => false,
                'external' => true
            ],
        ];
    }

    protected function resourcesNav()
    {
        return [
            [
                'label' => 'Members',
                'href' => route('members.index'),
                'active' => self::isActive('members.index')
            ],
            [
                'label' => 'Member Storage',
                'href' => route('storage_boxes.index'),
                'active' => self::isActive('storage_boxes.index')
            ],
            [
                'label' => 'Tools and Equipment',
                'href' => route('equipment.index'),
                'active' => self::isActive('equipment.index')
            ],
            [
                'label' => 'Stats',
                'href' => route('stats.index'),
                'active' => self::isActive('stats.index')
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
    }

    protected function adminNav()
    {
        $adminItems = [
            [
                'label' => '👮 Admin',
                'href' => route('admin'),
                'active' => self::isActive('admin'),
                'visible' => $this->user->isAdmin()
            ],
            [
                'label' => '👮 Manage Members',
                'href' => route('account.index'),
                'active' => self::isActive('account.index'),
                'visible' => $this->user->isAdmin()
            ],
            [
                'label' => '👮 Log Files',
                'href' => route('logs'),
                'active' => self::isActive('logs'),
                'visible' => $this->user->isAdmin()
            ],
            [
                'label' => '💌 Newsletter',
                'href' => route('newsletter'),
                'active' => self::isActive('newsletter'),
                'visible' => $this->user->isAdmin()
            ],
            [
                'label' => '💰 Payments',
                'href' => route('payments.index'),
                'active' => self::isActive('payments.index'),
                'visible' => $this->user->hasRole('finance') || $this->user->isAdmin()
            ]
        ];

        return array_filter($adminItems, function ($item) {
            return $item['visible'];
        });
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
