<?php

namespace Tests\Feature;

use BB\Entities\Payment;
use BB\Entities\Role;
use BB\Entities\User;
use BB\Support\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentListingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_payments_page_describes_each_payment()
    {
        $finance = User::factory()->create();
        $finance->assignRole(Role::findByName('finance'));
        $member = User::factory()->create();
        Payment::factory()->create([
            'user_id' => $member->id,
            'source' => 'cash',
            'source_id' => 'till-2',
            'reason' => 'consumables',
            'reference' => 'laser-ply',
            'amount' => 12.5,
            'status' => PaymentStatus::Withdrawn,
            'created_at' => '2024-03-05 10:00:00',
        ]);

        $this->actingAs($finance)->get('/payments')
            ->assertOk()
            ->assertSeeInOrder([
                '<td>Mar 5, 2024</td>',
                '<td>Consumables (laser-ply)</td>',
                '<td>Cash (till-2)</td>',
                '<td>£12.50</td>',
                '<td>laser-ply</td>',
                '<td>Paid</td>',
            ], false);
    }

    #[Test]
    public function the_balance_page_shows_spending_as_a_debit_and_top_ups_as_a_credit()
    {
        $member = User::factory()->create();
        Payment::factory()->create([
            'user_id' => $member->id,
            'source' => 'balance',
            'reason' => 'equipment-fee',
            'amount' => 3.5,
            'status' => PaymentStatus::Paid,
            'created_at' => '2024-03-06 10:00:00',
        ]);
        Payment::factory()->create([
            'user_id' => $member->id,
            'source' => 'gocardless-variable',
            'reason' => 'balance',
            'amount' => 10,
            'status' => PaymentStatus::Pending,
            'created_at' => '2024-03-05 10:00:00',
        ]);

        $this->actingAs($member)->get(route('account.balance.index', $member->id))
            ->assertOk()
            ->assertSeeInOrder([
                '<tr class="danger">',
                '<td >Equipment Costs</td>',
                '<td>BB Balance</td>',
                '<td>Mar 6, 2024</td>',
                '<td>-£3.5</td>',
                '<td>Paid</td>',
                '<tr class="success">',
                '<td >Balance Top Up</td>',
                '<td>Direct Debit</td>',
                '<td>Mar 5, 2024</td>',
                '<td>£10</td>',
                '<td>Pending confirmation</td>',
            ], false);
    }
}
