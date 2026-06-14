<?php

namespace Tests\Feature;

use BB\Entities\KeyFob;
use BB\Entities\Settings;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralInductionTest extends TestCase
{
    use RefreshDatabase;

    const CODE = 'OPEN-SESAME';

    protected function setUp(): void
    {
        parent::setUp();
        // The settings row is seeded by a migration, so update it rather than insert a duplicate.
        Settings::change('general_induction_code', self::CODE);
    }

    /** @test */
    public function completing_the_induction_with_the_correct_code_marks_the_member_inducted()
    {
        $user = factory(User::class)->create(['induction_completed' => false]);

        $this->actingAs($user)
            ->put(route('general-induction.update'), ['induction_code' => self::CODE])
            ->assertRedirect(route('account.show', $user->id));

        $this->assertTrue((bool) $user->fresh()->induction_completed);
    }

    /** @test */
    public function the_code_is_case_and_whitespace_insensitive()
    {
        $user = factory(User::class)->create(['induction_completed' => false]);

        $this->actingAs($user)
            ->put(route('general-induction.update'), ['induction_code' => '  open-sesame  '])
            ->assertRedirect();

        $this->assertTrue((bool) $user->fresh()->induction_completed);
    }

    /** @test */
    public function an_incorrect_code_does_not_complete_the_induction()
    {
        $user = factory(User::class)->create(['induction_completed' => false]);

        $this->from(route('general-induction.show'))
            ->actingAs($user)
            ->put(route('general-induction.update'), ['induction_code' => 'WRONG'])
            ->assertSessionHasErrors('induction_code');

        $this->assertFalse((bool) $user->fresh()->induction_completed);
    }

    /** @test */
    public function a_key_fob_can_be_registered_during_induction()
    {
        $user = factory(User::class)->create(['induction_completed' => false]);
        $keyId = sprintf('%08X', 0xCAFEBABE);

        $this->actingAs($user)
            ->put(route('general-induction.update'), [
                'induction_code' => self::CODE,
                'key_id' => $keyId,
            ])
            ->assertRedirect();

        $this->assertTrue((bool) $user->fresh()->induction_completed);
        $this->assertDatabaseHas('key_fobs', ['user_id' => $user->id, 'key_id' => $keyId]);
    }
}
