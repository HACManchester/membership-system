<?php

namespace Tests\Feature;

use BB\Entities\Equipment;
use BB\Entities\Induction;
use BB\Entities\User;
use BB\Events\Inductions\InductionCompletedEvent;
use BB\Events\Inductions\InductionMarkedAsTrainerEvent;
use BB\Events\Inductions\InductionRequestedEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class InductionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $trainer;
    protected $regularUser;
    protected $anotherUser;
    protected $equipment;
    protected $pendingInduction;
    protected $trainedInduction;
    protected $trainerInduction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTestData();
    }

    protected function setUpTestData(): void
    {
        // Create users
        $this->admin = factory(User::class)->state('admin')->create();
        $this->trainer = factory(User::class)->create();
        $this->regularUser = factory(User::class)->create();
        $this->anotherUser = factory(User::class)->create();

        // Create equipment
        $this->equipment = factory(Equipment::class)->create([
            'name' => 'Test Equipment',
            'slug' => 'test-equipment',
            'requires_induction' => true,
            'induction_category' => 'test-equipment',
            'accepting_inductions' => true,
        ]);

        // Create trainer induction
        $this->trainerInduction = new Induction([
            'key' => 'test-equipment',
            'user_id' => $this->trainer->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => true,
            'trainer_user_id' => $this->admin->id,
        ]);
        $this->trainerInduction->save();

        // Create pending induction
        $this->pendingInduction = new Induction([
            'key' => 'test-equipment',
            'user_id' => $this->regularUser->id,
            'trained' => null,
            'active' => false,
            'is_trainer' => false,
            'trainer_user_id' => null,
        ]);
        $this->pendingInduction->save();

        // Create trained induction
        $this->trainedInduction = new Induction([
            'key' => 'test-equipment',
            'user_id' => $this->anotherUser->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => false,
            'trainer_user_id' => $this->trainer->id,
        ]);
        $this->trainedInduction->save();
    }

    /** @test */
    public function user_can_request_own_training()
    {
        Event::fake();

        $newUser = factory(User::class)->create();

        $response = $this->actingAs($newUser)
            ->post(route('equipment_training.create', $this->equipment));

        $response->assertRedirect(route('equipment.show', $this->equipment));

        $this->assertDatabaseHas('inductions', [
            'user_id' => $newUser->id,
            'key' => 'test-equipment',
            'trained' => null,
            'is_trainer' => false,
        ]);

        Event::assertDispatched(InductionRequestedEvent::class, function ($event) use ($newUser) {
            return $event->induction->user_id === $newUser->id &&
                   $event->induction->key === 'test-equipment';
        });
    }

    /** @test */
    public function trainer_can_request_training_for_others()
    {
        Event::fake();

        $newUser = factory(User::class)->create();

        $response = $this->actingAs($this->trainer)
            ->post(route('equipment_training.create', $this->equipment), [
                'user_id' => $newUser->id,
            ]);

        $response->assertRedirect(route('equipment.show', $this->equipment));

        $this->assertDatabaseHas('inductions', [
            'user_id' => $newUser->id,
            'key' => 'test-equipment',
            'trained' => null,
            'is_trainer' => false,
        ]);

        Event::assertDispatched(InductionRequestedEvent::class);
    }

    /** @test */
    public function admin_can_request_training_for_others()
    {
        Event::fake();

        $newUser = factory(User::class)->create();

        $response = $this->actingAs($this->admin)
            ->post(route('equipment_training.create', $this->equipment), [
                'user_id' => $newUser->id,
            ]);

        $response->assertRedirect(route('equipment.show', $this->equipment));

        $this->assertDatabaseHas('inductions', [
            'user_id' => $newUser->id,
            'key' => 'test-equipment',
        ]);

        Event::assertDispatched(InductionRequestedEvent::class);
    }

    /** @test */
    public function regular_user_cannot_request_training_for_others()
    {
        $newUser = factory(User::class)->create();

        $response = $this->actingAs($this->regularUser)
            ->post(route('equipment_training.create', $this->equipment), [
                'user_id' => $newUser->id,
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('inductions', [
            'user_id' => $newUser->id,
            'key' => 'test-equipment',
        ]);
    }

    /** @test */
    public function trainer_can_mark_user_as_trained()
    {
        Event::fake();

        $response = $this->actingAs($this->trainer)
            ->post(route('equipment_training.train', [$this->equipment, $this->pendingInduction]), [
                'trainer_user_id' => $this->trainer->id,
            ]);

        $response->assertRedirect(route('equipment.show', $this->equipment));

        $this->pendingInduction->refresh();
        $this->assertNotNull($this->pendingInduction->trained);
        $this->assertEquals($this->trainer->id, $this->pendingInduction->trainer_user_id);

        Event::assertDispatched(InductionCompletedEvent::class, function ($event) {
            return $event->induction->id === $this->pendingInduction->id;
        });
    }

    /** @test */
    public function admin_can_mark_user_as_trained()
    {
        Event::fake();

        $response = $this->actingAs($this->admin)
            ->post(route('equipment_training.train', [$this->equipment, $this->pendingInduction]), [
                'trainer_user_id' => $this->admin->id,
            ]);

        $response->assertRedirect(route('equipment.show', $this->equipment));

        $this->pendingInduction->refresh();
        $this->assertNotNull($this->pendingInduction->trained);
        $this->assertEquals($this->admin->id, $this->pendingInduction->trainer_user_id);

        Event::assertDispatched(InductionCompletedEvent::class);
    }

    /** @test */
    public function regular_user_cannot_mark_user_as_trained()
    {
        $response = $this->actingAs($this->regularUser)
            ->post(route('equipment_training.train', [$this->equipment, $this->pendingInduction]), [
                'trainer_user_id' => $this->regularUser->id,
            ]);

        $response->assertStatus(403);

        $this->pendingInduction->refresh();
        $this->assertNull($this->pendingInduction->trained);
    }

    /** @test */
    public function trainer_can_untrain_user()
    {
        $response = $this->actingAs($this->trainer)
            ->post(route('equipment_training.untrain', [$this->equipment, $this->trainedInduction]));

        $response->assertRedirect(route('equipment.show', $this->equipment));

        $this->trainedInduction->refresh();
        $this->assertNull($this->trainedInduction->trained);
        $this->assertEquals(0, $this->trainedInduction->trainer_user_id);
    }

    /** @test */
    public function admin_can_untrain_user()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('equipment_training.untrain', [$this->equipment, $this->trainedInduction]));

        $response->assertRedirect(route('equipment.show', $this->equipment));

        $this->trainedInduction->refresh();
        $this->assertNull($this->trainedInduction->trained);
    }

    /** @test */
    public function regular_user_cannot_untrain_user()
    {
        $response = $this->actingAs($this->regularUser)
            ->post(route('equipment_training.untrain', [$this->equipment, $this->trainedInduction]));

        $response->assertStatus(403);

        $this->trainedInduction->refresh();
        $this->assertNotNull($this->trainedInduction->trained);
    }

    /** @test */
    public function trainer_can_promote_user_to_trainer()
    {
        Event::fake();

        $response = $this->actingAs($this->trainer)
            ->post(route('equipment_training.promote', [$this->equipment, $this->trainedInduction]));

        $response->assertRedirect(route('equipment.show', $this->equipment));

        $this->trainedInduction->refresh();
        $this->assertTrue($this->trainedInduction->is_trainer);

        Event::assertDispatched(InductionMarkedAsTrainerEvent::class, function ($event) {
            return $event->induction->id === $this->trainedInduction->id;
        });
    }

    /** @test */
    public function admin_can_promote_user_to_trainer()
    {
        Event::fake();

        $response = $this->actingAs($this->admin)
            ->post(route('equipment_training.promote', [$this->equipment, $this->trainedInduction]));

        $response->assertRedirect(route('equipment.show', $this->equipment));

        $this->trainedInduction->refresh();
        $this->assertTrue($this->trainedInduction->is_trainer);

        Event::assertDispatched(InductionMarkedAsTrainerEvent::class);
    }

    /** @test */
    public function regular_user_cannot_promote_user_to_trainer()
    {
        $response = $this->actingAs($this->regularUser)
            ->post(route('equipment_training.promote', [$this->equipment, $this->trainedInduction]));

        $response->assertStatus(403);

        $this->trainedInduction->refresh();
        $this->assertFalse($this->trainedInduction->is_trainer);
    }

    /** @test */
    public function trainer_can_demote_trainer()
    {
        // First promote the user to trainer
        $this->trainedInduction->update(['is_trainer' => true]);

        $response = $this->actingAs($this->trainer)
            ->post(route('equipment_training.demote', [$this->equipment, $this->trainedInduction]));

        $response->assertRedirect(route('equipment.show', $this->equipment));

        $this->trainedInduction->refresh();
        $this->assertFalse($this->trainedInduction->is_trainer);
    }

    /** @test */
    public function admin_can_demote_trainer()
    {
        // First promote the user to trainer
        $this->trainedInduction->update(['is_trainer' => true]);

        $response = $this->actingAs($this->admin)
            ->post(route('equipment_training.demote', [$this->equipment, $this->trainedInduction]));

        $response->assertRedirect(route('equipment.show', $this->equipment));

        $this->trainedInduction->refresh();
        $this->assertFalse($this->trainedInduction->is_trainer);
    }

    /** @test */
    public function regular_user_cannot_demote_trainer()
    {
        // First promote the user to trainer
        $this->trainedInduction->update(['is_trainer' => true]);

        $response = $this->actingAs($this->regularUser)
            ->post(route('equipment_training.demote', [$this->equipment, $this->trainedInduction]));

        $response->assertStatus(403);

        $this->trainedInduction->refresh();
        $this->assertTrue($this->trainedInduction->is_trainer);
    }

    /** @test */
    public function trainer_can_delete_induction()
    {
        $inductionId = $this->pendingInduction->id;

        $response = $this->actingAs($this->trainer)
            ->delete(route('equipment_training.destroy', [$this->equipment, $this->pendingInduction]));

        $response->assertRedirect(route('equipment.show', $this->equipment));

        $this->assertDatabaseMissing('inductions', ['id' => $inductionId]);
    }

    /** @test */
    public function admin_can_delete_induction()
    {
        $inductionId = $this->pendingInduction->id;

        $response = $this->actingAs($this->admin)
            ->delete(route('equipment_training.destroy', [$this->equipment, $this->pendingInduction]));

        $response->assertRedirect(route('equipment.show', $this->equipment));

        $this->assertDatabaseMissing('inductions', ['id' => $inductionId]);
    }

    /** @test */
    public function regular_user_cannot_delete_induction()
    {
        $inductionId = $this->pendingInduction->id;

        $response = $this->actingAs($this->regularUser)
            ->delete(route('equipment_training.destroy', [$this->equipment, $this->pendingInduction]));

        $response->assertStatus(403);

        $this->assertDatabaseHas('inductions', ['id' => $inductionId]);
    }

    /** @test */
    public function non_trainer_for_different_equipment_cannot_train()
    {
        // Create trainer for different equipment
        $otherTrainer = factory(User::class)->create();
        $otherTrainerInduction = new Induction([
            'key' => 'other-equipment',
            'user_id' => $otherTrainer->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => true,
            'trainer_user_id' => $this->admin->id,
        ]);
        $otherTrainerInduction->save();

        // Other trainer should not be able to train for our equipment
        $response = $this->actingAs($otherTrainer)
            ->post(route('equipment_training.train', [$this->equipment, $this->pendingInduction]), [
                'trainer_user_id' => $otherTrainer->id,
            ]);

        $response->assertStatus(403);
    }

}