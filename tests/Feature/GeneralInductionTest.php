<?php

namespace Tests\Feature;

use BB\Entities\KeyFob;
use BB\Entities\Settings;
use BB\Entities\User;
use BB\Support\SettingKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GeneralInductionTest extends TestCase
{
    use RefreshDatabase;

    const CODE = 'OPEN-SESAME';

    protected function setUp(): void
    {
        parent::setUp();
        Settings::set(SettingKey::GeneralInductionCode, self::CODE);
    }

    #[Test]
    public function completing_the_induction_with_the_correct_code_marks_the_member_inducted()
    {
        $user = User::factory()->create(['induction_completed' => false]);

        $this->actingAs($user)
            ->put(route('general-induction.update'), ['induction_code' => self::CODE])
            ->assertRedirect(route('account.show', $user->id));

        $this->assertTrue((bool) $user->fresh()->induction_completed);
    }

    #[Test]
    public function the_code_is_case_and_whitespace_insensitive()
    {
        $user = User::factory()->create(['induction_completed' => false]);

        $this->actingAs($user)
            ->put(route('general-induction.update'), ['induction_code' => '  open-sesame  '])
            ->assertRedirect();

        $this->assertTrue((bool) $user->fresh()->induction_completed);
    }

    #[Test]
    public function an_incorrect_code_does_not_complete_the_induction()
    {
        $user = User::factory()->create(['induction_completed' => false]);

        $this->from(route('general-induction.show'))
            ->actingAs($user)
            ->put(route('general-induction.update'), ['induction_code' => 'WRONG'])
            ->assertSessionHasErrors('induction_code');

        $this->assertFalse((bool) $user->fresh()->induction_completed);
    }

    #[Test]
    public function a_key_fob_can_be_registered_during_induction()
    {
        $user = User::factory()->create(['induction_completed' => false]);
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

    /**
     * Inducted members give the induction, so the page shows them the code.
     */
    #[Test]
    public function the_page_shows_an_inducted_member_the_code_escaped()
    {
        Settings::set(SettingKey::GeneralInductionCode, '<b>OPEN</b>');
        $user = User::factory()->create(['induction_completed' => true]);

        $response = $this->actingAs($user)->get(route('general-induction.show'));

        $response->assertOk();
        $response->assertSee('&lt;b&gt;OPEN&lt;/b&gt;', false);
        $response->assertDontSee('<b>OPEN</b>', false);
    }

    #[Test]
    public function the_page_does_not_show_the_code_to_a_member_who_is_not_inducted()
    {
        $user = User::factory()->create(['induction_completed' => false]);

        $response = $this->actingAs($user)->get(route('general-induction.show'));

        $response->assertOk();
        $response->assertDontSee(self::CODE);
    }
}
