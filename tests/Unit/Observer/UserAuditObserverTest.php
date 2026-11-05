<?php

namespace Tests\Unit\Observer;

use BB\Entities\AuditLog;
use BB\Entities\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The audit text records the stored values (0/1) of the audited flags, as it
 * did before they were cast to booleans.
 */
class UserAuditObserverTest extends TestCase
{
    #[Test]
    public function removing_trusted_status_records_the_old_value_as_1()
    {
        $member = User::factory()->create(['trusted' => 1]);

        $member->fresh()->forceFill(['trusted' => 0])->save();

        $this->assertDatabaseHas(AuditLog::class, ['user_id' => $member->id, 'description' => 'trusted [is 0, was 1]']);
    }

    #[Test]
    public function completing_the_induction_records_the_old_value_as_0()
    {
        $member = User::factory()->create(['induction_completed' => 0]);
        $member = $member->fresh();

        $member->induction_completed = true;
        $member->save();

        $this->assertDatabaseHas(AuditLog::class, ['user_id' => $member->id, 'description' => 'induction_completed [is 1, was 0]']);
    }
}
