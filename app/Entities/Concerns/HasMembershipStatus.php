<?php

namespace BB\Entities\Concerns;

use BB\Support\MemberStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Reading a member's membership status. Changing it is
 * BB\Services\MembershipStatus's job.
 */
trait HasMembershipStatus
{
    public function isActive(): bool
    {
        return $this->active && !$this->banned;
    }

    public function isBanned(): bool
    {
        return !!$this->banned;
    }

    public function hasPaymentWarning(): bool
    {
        return $this->status == MemberStatus::PaymentWarning;
    }

    public function isSuspended(): bool
    {
        return $this->status == MemberStatus::Suspended;
    }

    /**
     * Can the user see protected member photos?
     * Only available to active members
     */
    public function shouldMemberSeeProtectedPhoto(): bool
    {
        return in_array($this->status, [
            MemberStatus::Active,
            MemberStatus::PaymentWarning,
            MemberStatus::Honorary,
        ], true);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where(['active' => true, 'banned' => false]);
    }

    #[Scope]
    protected function notSpecialCase(Builder $query): void
    {
        $query->where('status', '!=', MemberStatus::Honorary)
            ->where(function ($subquery) {
                $subquery->whereNull('gift_expires')->orWhere('gift_expires', '<=', Carbon::today());
            });
    }

    #[Scope]
    protected function leaving(Builder $query): void
    {
        $query->where('status', MemberStatus::Leaving);
    }

    #[Scope]
    protected function paymentWarning(Builder $query): void
    {
        $query->where('status', MemberStatus::PaymentWarning);
    }

    #[Scope]
    protected function suspended(Builder $query): void
    {
        $query->where('status', MemberStatus::Suspended);
    }

    #[Scope]
    protected function recentlyLapsed(Builder $query): void
    {
        $query->whereActive(false)
            ->where('banned', false)
            ->where('subscription_expires', '>=', Carbon::today()->subMonths(6));
    }
}
