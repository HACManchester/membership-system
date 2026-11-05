<?php

namespace Tests\Feature;

use BB\Entities\Role;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CashPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function admin()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findByName('admin'));

        return $user;
    }

    #[Test]
    public function an_admin_can_record_a_cash_payment_for_a_member()
    {
        $member = User::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('account.payment.cash.create', $member->id), [
                'amount' => 5,
                'reason' => 'balance',
                'source_id' => 'test-source',
            ])
            ->assertRedirect(route('account.balance.index', $member->id))
            ->assertSessionHas('success', 'Top Up successful')
            ->assertSessionMissing('flash_notification.level');

        $this->assertDatabaseHas('payments', [
            'user_id' => $member->id,
            'source' => 'cash',
            'reason' => 'balance',
            'amount' => 5,
        ]);
    }

    #[Test]
    public function a_non_admin_cannot_record_a_cash_payment()
    {
        $member = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($member)
            ->post(route('account.payment.cash.create', $target->id), [
                'amount' => 5,
                'reason' => 'balance',
                'source_id' => 'test-source',
            ])
            ->assertStatus(403);

        $this->assertEquals(0, $target->fresh()->payments()->count());
    }

    #[Test]
    public function a_top_up_from_the_account_page_returns_to_it()
    {
        $member = User::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('account.payment.cash.create', $member->id), [
                'amount' => 5,
                'reason' => 'balance',
                'source_id' => 'test-source',
                'return_to' => 'account',
            ])
            ->assertRedirect(route('account.show', $member->id));
    }

    #[Test]
    public function a_withdrawal_from_the_account_page_returns_to_it()
    {
        $member = User::factory()->create(['cash_balance' => 1000]);

        $this->actingAs($this->admin())
            ->delete(route('account.payment.cash.destroy', $member->id), [
                'amount' => 2,
                'ref' => 'snacks',
                'return_to' => 'account',
            ])
            ->assertRedirect(route('account.show', $member->id))
            ->assertSessionHas('success', 'Payment recorded');

        $this->assertDatabaseHas('payments', [
            'user_id' => $member->id,
            'source' => 'balance',
            'reason' => 'withdrawal',
            'amount' => 2,
        ]);
    }

    #[DataProvider('unknownReturnPages')]
    #[Test]
    public function a_top_up_with_an_unknown_return_page_is_rejected(string $returnTo)
    {
        $member = User::factory()->create();

        $this->actingAs($this->admin())
            ->from(route('account.show', $member->id))
            ->post(route('account.payment.cash.create', $member->id), [
                'amount' => 5,
                'reason' => 'balance',
                'source_id' => 'test-source',
                'return_to' => $returnTo,
            ])
            ->assertRedirect(route('account.show', $member->id))
            ->assertSessionHasErrorsIn('credit', 'return_to');

        $this->assertEquals(0, $member->fresh()->payments()->count());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function unknownReturnPages(): array
    {
        return [
            'absolute URL' => ['https://evil.example'],
            'protocol-relative URL' => ['//evil.example'],
            'a path' => ['account/1'],
            'javascript URL' => ['javascript:alert(1)'],
        ];
    }
}
