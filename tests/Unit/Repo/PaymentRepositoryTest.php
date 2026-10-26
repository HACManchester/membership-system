<?php

namespace Tests\Unit\Repo;

use BB\Entities\Payment;
use BB\Entities\User;
use BB\Exceptions\PaymentException;
use BB\Repo\PaymentRepository;
use Tests\TestCase;

class PaymentRepositoryTest extends TestCase
{
    private $paymentRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->paymentRepository = app(PaymentRepository::class);
    }

    public function testRecordPaymentRejectsSourceIdAlreadyUsedByAnotherUser()
    {
        $existingUser = User::factory()->create();
        $otherUser = User::factory()->create();

        Payment::factory()->create([
            'user_id' => $existingUser->id,
            'source' => 'gocardless-variable',
            'source_id' => 'PM123456789',
            'reason' => 'subscription',
            'status' => 'pending',
        ]);

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessageIsOrContains('already uses it');

        $this->paymentRepository->recordPayment(
            'subscription',
            $otherUser->id,
            'gocardless-variable',
            'PM123456789',
            22,
            'failed'
        );
    }

    public function testRecordPaymentReturnsExistingRecordForSameUserAndSourceId()
    {
        $user = User::factory()->create();

        $existing = Payment::factory()->create([
            'user_id' => $user->id,
            'source' => 'gocardless-variable',
            'source_id' => 'PM123456789',
            'reason' => 'subscription',
            'status' => 'pending',
        ]);

        $paymentId = $this->paymentRepository->recordPayment(
            'subscription',
            $user->id,
            'gocardless-variable',
            'PM123456789',
            22,
            'pending'
        );

        $this->assertEquals($existing->id, $paymentId);
        $this->assertEquals(1, Payment::where('source_id', 'PM123456789')->count());
    }

    public function testRecordPaymentAllowsPaymentsWithoutSourceId()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $id1 = $this->paymentRepository->recordPayment('subscription', $user1->id, 'gocardless-variable', null, 22, 'failed');
        $id2 = $this->paymentRepository->recordPayment('subscription', $user2->id, 'gocardless-variable', null, 17, 'failed');

        $this->assertNotEquals($id1, $id2);
    }

    public function testGetPaymentBySourceIdReturnsOldestRecord()
    {
        $user = User::factory()->create();

        $first = Payment::factory()->create([
            'user_id' => $user->id,
            'source' => 'gocardless-variable',
            'source_id' => 'PM123456789',
            'reason' => 'subscription',
            'status' => 'pending',
        ]);
        Payment::factory()->create([
            'user_id' => $user->id,
            'source' => 'gocardless-variable',
            'source_id' => 'PM123456789',
            'reason' => 'subscription',
            'status' => 'failed',
        ]);

        $found = $this->paymentRepository->getPaymentBySourceId('PM123456789');

        $this->assertEquals($first->id, $found->id);
    }
}
