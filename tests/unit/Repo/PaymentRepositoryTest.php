<?php

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
        $existingUser = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        factory(Payment::class)->create([
            'user_id' => $existingUser->id,
            'source' => 'gocardless-variable',
            'source_id' => 'PM123456789',
            'reason' => 'subscription',
            'status' => 'pending',
        ]);

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('already uses it');

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
        $user = factory(User::class)->create();

        $existing = factory(Payment::class)->create([
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
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();

        $id1 = $this->paymentRepository->recordPayment('subscription', $user1->id, 'gocardless-variable', null, 22, 'failed');
        $id2 = $this->paymentRepository->recordPayment('subscription', $user2->id, 'gocardless-variable', null, 17, 'failed');

        $this->assertNotEquals($id1, $id2);
    }

    public function testGetPaymentBySourceIdReturnsOldestRecord()
    {
        $user = factory(User::class)->create();

        $first = factory(Payment::class)->create([
            'user_id' => $user->id,
            'source' => 'gocardless-variable',
            'source_id' => 'PM123456789',
            'reason' => 'subscription',
            'status' => 'pending',
        ]);
        factory(Payment::class)->create([
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
