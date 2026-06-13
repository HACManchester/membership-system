<?php

use BB\Entities\Equipment;
use BB\Entities\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\BrowserKitTestCase;

class InductionTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    public function test_can_request_own_induction()
    {
        $user = factory(User::class)->create();

        $equipment = factory(Equipment::class)->state('requiresInduction')->create();

        $this->actingAs($user);

        $this->visit(route('equipment.show', $equipment))
            ->press('Request induction')
            ->see("Training to be completed");
    }


}
