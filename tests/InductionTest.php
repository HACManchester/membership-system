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

    public function x_test_trainers_can_request_inductions_for_others() {}

    public function x_test_trainers_can_mark_as_trained() {}

    public function x_test_trainers_can_promote_others_to_trainer() {}
}
