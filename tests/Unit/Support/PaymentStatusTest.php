<?php

namespace Tests\Unit\Support;

use BB\Support\PaymentStatus;
use Illuminate\Support\Facades\Exceptions;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    #[Test]
    public function a_status_gocardless_documents_maps_to_its_case()
    {
        Exceptions::fake();

        $this->assertSame(PaymentStatus::CustomerApprovalDenied, PaymentStatus::fromGoCardless('customer_approval_denied', PaymentStatus::Pending));
        Exceptions::assertNothingReported();
    }

    #[Test]
    public function an_unknown_gocardless_status_falls_back_and_is_reported()
    {
        Exceptions::fake();

        $this->assertSame(PaymentStatus::Failed, PaymentStatus::fromGoCardless('vanished', PaymentStatus::Failed));
        Exceptions::assertReported(fn (RuntimeException $e) => str_contains($e->getMessage(), '"vanished"'));
    }
}
