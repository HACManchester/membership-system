<?php

namespace Tests\Feature\Migrations;

use BB\Entities\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WidenPaymentsStatusTest extends TestCase
{
    #[Test]
    public function migrating_restores_gocardless_statuses_that_were_cut_short()
    {
        $truncated = $this->paymentWithStatus('pending_customer_app');
        $paid = $this->paymentWithStatus('paid');

        $this->migration()->up();

        $this->assertSame('pending_customer_approval', $this->storedStatus($truncated));
        $this->assertSame('paid', $this->storedStatus($paid));
    }

    #[Test]
    public function rolling_back_cuts_long_statuses_back_to_20_characters()
    {
        $long = $this->paymentWithStatus('pending_customer_approval');
        $paid = $this->paymentWithStatus('paid');

        $this->migration()->down();

        $this->assertSame('pending_customer_app', $this->storedStatus($long));
        $this->assertSame('paid', $this->storedStatus($paid));
    }

    private function migration(): Migration
    {
        return require database_path('migrations/2026_11_04_100001_widen_payments_status.php');
    }

    /**
     * Inserted directly, as a status cut short isn't one the app would write
     */
    private function paymentWithStatus(string $status): int
    {
        return DB::table('payments')->insertGetId(Payment::factory()->raw(['status' => $status]));
    }

    private function storedStatus(int $paymentId): string
    {
        return DB::table('payments')->where('id', $paymentId)->value('status');
    }
}
