<?php

namespace Tests\Feature;

use BB\Entities\Role;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
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
                'amount' => '12.34', // pounds
                'reason' => 'balance',
                'source_id' => 'test-source',
                'return_path' => "/account/{$member->id}",
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('payments', [
            'user_id' => $member->id,
            'source' => 'cash',
            'reason' => 'balance',
            'amount' => 1234,
        ]);
        $this->assertSame(1234, $member->fresh()->cash_balance);
    }

    #[Test]
    public function a_cash_payment_with_fractions_of_a_penny_is_rejected()
    {
        $member = User::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('account.payment.cash.create', $member->id), [
                'amount' => '12.345',
                'reason' => 'balance',
                'source_id' => 'test-source',
                'return_path' => "/account/{$member->id}",
            ])
            ->assertSessionHasErrorsIn('credit', 'amount');

        $this->assertEquals(0, $member->fresh()->payments()->count());
    }

    #[Test]
    public function a_cash_payment_over_a_thousand_pounds_is_rejected()
    {
        $member = User::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('account.payment.cash.create', $member->id), [
                'amount' => '1000.01',
                'reason' => 'balance',
                'source_id' => 'test-source',
                'return_path' => "/account/{$member->id}",
            ])
            ->assertSessionHasErrorsIn('credit', ['amount' => 'The most we can credit at once is £1,000.']);

        $this->assertEquals(0, $member->fresh()->payments()->count());
    }

    /**
     * A member may go up to £20 overdrawn
     */
    #[Test]
    #[TestWith(['25.50', true])] // a £5.50 balance plus the £20
    #[TestWith(['25.51', false])]
    public function a_withdrawal_can_take_the_balance_up_to_the_overdraft(string $pounds, bool $allowed)
    {
        $member = User::factory()->create(['cash_balance' => 550]);

        $response = $this->actingAs($this->admin())
            ->delete(route('account.payment.cash.destroy', $member->id), [
                'amount' => $pounds,
                'ref' => 'snacks',
                'return_path' => "/account/{$member->id}",
            ]);

        if ($allowed) {
            $response->assertSessionHasNoErrors();
            $this->assertDatabaseHas('payments', ['user_id' => $member->id, 'reason' => 'withdrawal', 'amount' => 2550]);
        } else {
            $response->assertSessionHasErrorsIn('withdrawal', 'amount');
            $this->assertEquals(0, $member->fresh()->payments()->count());
        }
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
                'return_path' => "/account/{$target->id}",
            ])
            ->assertStatus(403);

        $this->assertEquals(0, $target->fresh()->payments()->count());
    }
}
