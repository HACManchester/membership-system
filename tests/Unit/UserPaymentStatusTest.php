<?php

namespace Tests\Unit;

use BB\Entities\User;
use Tests\TestCase;

class UserPaymentStatusTest extends TestCase
{
    public function testAPaymentDayAfterThe28thBecomesThe1st()
    {
        $user = User::factory()->create(['status' => 'active']);

        $user->payment_day = 29;
        $user->save();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'payment_day' => 1]);

        $user->payment_day = 28;
        $user->save();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'payment_day' => 28]);
    }
}
