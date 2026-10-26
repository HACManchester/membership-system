<?php

namespace Tests\Feature;

use BB\Entities\Role;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
                'return_path' => "/account/{$member->id}",
            ])
            ->assertStatus(302);

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
                'return_path' => "/account/{$target->id}",
            ])
            ->assertStatus(403);

        $this->assertEquals(0, $target->fresh()->payments()->count());
    }
}
