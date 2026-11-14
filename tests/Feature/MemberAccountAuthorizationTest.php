<?php

namespace Tests\Feature;

use BB\Entities\ProfileData;
use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Who may use each endpoint that acts on a member's account: the member
 * themselves, another member, and an admin. Admin-only routes also turn the
 * member away in their route middleware.
 */
class MemberAccountAuthorizationTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string, 2: array<string, mixed>, 3: int, 4: int, 5: int}>
     *     method, route name, payload, then the status for the member, another member and an admin
     */
    public static function accountEndpoints(): array
    {
        return [
            'account page' => ['get', 'account.show', [], 200, 403, 200],
            'account edit form' => ['get', 'account.edit', [], 200, 403, 200],
            'account update' => ['put', 'account.update', [], 302, 403, 302],
            'account deletion' => ['delete', 'account.destroy', [], 302, 403, 302],
            'subscription amount' => ['post', 'account.update-sub-payment', ['monthly_subscription' => 3000], 302, 403, 302],
            'direct debit setup' => ['get', 'account.subscription.create', [], 302, 403, 302],
            'direct debit confirmation' => ['get', 'account.subscription.store', [], 302, 403, 302],
            'direct debit cancellation' => ['delete', 'account.subscription.destroy', [], 302, 403, 302],
            'experimental subscription' => ['post', 'account.subscription.experimental.store', [], 403, 403, 302],
            'experimental subscription cancellation' => ['delete', 'account.subscription.experimental.destroy', [], 403, 403, 302],
            'balance page' => ['get', 'account.balance.index', [], 200, 403, 200],
            'profile edit form' => ['get', 'account.profile.edit', [], 200, 403, 200],
            'address approval' => ['put', 'account.address-approval', [], 403, 403, 302],
            'admin member update' => ['put', 'account.admin-update', [], 403, 403, 302],
            'manual payment' => ['post', 'account.payment.store', [], 403, 403, 302],
            'photo approval' => ['put', 'account.photo-approval', [], 403, 403, 302],
            'cash top up' => ['post', 'account.payment.cash.create', [], 403, 403, 302],
            'cash withdrawal' => ['delete', 'account.payment.cash.destroy', [], 403, 403, 302],
        ];
    }

    #[DataProvider('accountEndpoints')]
    #[Test]
    public function the_member_can_use_the_endpoint_only_as_its_route_allows(string $method, string $route, array $payload, int $self, int $other, int $admin)
    {
        $member = $this->member();
        $this->fakeGoCardless();

        $this->actingAs($member)->call(strtoupper($method), $this->url($route, $member), $payload)->assertStatus($self);
    }

    #[DataProvider('accountEndpoints')]
    #[Test]
    public function another_member_is_refused(string $method, string $route, array $payload, int $self, int $other, int $admin)
    {
        $member = $this->member();
        $this->fakeGoCardless();

        $this->actingAs($this->member())->call(strtoupper($method), $this->url($route, $member), $payload)->assertStatus($other);
    }

    #[DataProvider('accountEndpoints')]
    #[Test]
    public function an_admin_can_act_for_the_member(string $method, string $route, array $payload, int $self, int $other, int $admin)
    {
        $member = $this->member();
        $this->fakeGoCardless();

        $this->actingAs(User::factory()->admin()->create())->call(strtoupper($method), $this->url($route, $member), $payload)->assertStatus($admin);
    }

    private function member(): User
    {
        $member = User::factory()->create(['monthly_subscription' => null]);
        ProfileData::factory()->create(['user_id' => $member->id]);

        return $member;
    }

    private function url(string $route, User $member): string
    {
        return $route === 'account.subscription.destroy'
            ? route($route, [$member->id, 1])
            : route($route, $member->id);
    }

    /**
     * Nothing here should reach GoCardless, but the subscription endpoints that
     * an admin may use do call it.
     */
    private function fakeGoCardless(): void
    {
        $this->mock(GoCardlessHelper::class, function ($mock) {
            $mock->shouldReceive('confirmResource')->andThrow(new \Exception('Not confirmed'));
            $mock->shouldReceive('createSubscription')->andReturn((object) ['id' => 'SB123']);
            $mock->shouldReceive('cancelSubscription')->andReturn((object) ['status' => 'cancelled']);
        });
    }
}
