<?php

namespace Tests\Feature;

use BB\Entities\Settings;
use BB\Entities\User;
use BB\Support\SettingKey;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    private const INDUCTION_CODE = 'SECRET-INDUCT-4471';

    #[Test]
    public function a_guest_is_sent_to_login()
    {
        $this->setInductionCode();

        $this->get(route('settings.index'))->assertRedirect(route('login'));
        $this->put(route('settings.update', 'general_induction_code'), ['setting_value' => 'GUESS'])
            ->assertRedirect(route('login'));

        $this->assertSame(self::INDUCTION_CODE, Settings::get(SettingKey::GeneralInductionCode));
    }

    #[Test]
    public function a_member_cannot_view_the_settings()
    {
        $this->setInductionCode();
        $member = User::factory()->create();

        $response = $this->actingAs($member)->get(route('settings.index'));

        $response->assertForbidden();
        $response->assertDontSee(self::INDUCTION_CODE);
    }

    #[Test]
    public function a_member_cannot_change_a_setting()
    {
        $this->setInductionCode();
        $member = User::factory()->create();

        $response = $this->actingAs($member)
            ->put(route('settings.update', 'general_induction_code'), ['setting_value' => 'MEMBER-CODE']);

        $response->assertForbidden();
        $this->assertSame(self::INDUCTION_CODE, Settings::get(SettingKey::GeneralInductionCode));
    }

    #[Test]
    public function an_admin_sees_every_known_setting_with_its_value()
    {
        $this->setInductionCode();
        DB::table('settings')->insert(['key' => 'some_legacy_setting', 'value' => 'not-listed']);
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('settings.index'));

        $response->assertOk();
        $response->assertInertia(function ($page) {
            $page->component('Admin/Settings/Index')
                ->has('settings', 1)
                ->where('settings.0.key', 'general_induction_code')
                ->where('settings.0.label', 'General induction code')
                ->where('settings.0.value', self::INDUCTION_CODE)
                ->where('settings.0.update_url', '/admin/settings/general_induction_code');
        });
    }

    #[Test]
    public function a_known_setting_with_no_row_is_listed_as_not_set()
    {
        DB::table('settings')->where('key', 'general_induction_code')->delete();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('settings.index'));

        $response->assertInertia(function ($page) {
            $page->where('settings.0.key', 'general_induction_code')
                ->where('settings.0.value', null)
                ->where('settings.0.updated_by', null);
        });
    }

    #[Test]
    public function an_admin_can_change_a_setting()
    {
        $this->setInductionCode();
        $admin = User::factory()->admin()->create();
        $this->travelTo('2026-10-26 12:00:00');

        $response = $this->actingAs($admin)
            ->put(route('settings.update', 'general_induction_code'), ['setting_value' => 'NEW-CODE-2026']);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success', 'General induction code updated.');
        $this->assertDatabaseHas('settings', [
            'key' => 'general_induction_code',
            'value' => 'NEW-CODE-2026',
            'updated_by' => $admin->id,
            'updated_at' => '2026-10-26 12:00:00',
        ]);
    }

    #[Test]
    public function an_admin_can_set_a_known_setting_whose_row_is_missing()
    {
        DB::table('settings')->where('key', 'general_induction_code')->delete();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('settings.update', 'general_induction_code'), ['setting_value' => 'FRESH-CODE']);

        $this->assertDatabaseHas('settings', [
            'key' => 'general_induction_code',
            'value' => 'FRESH-CODE',
            'updated_by' => $admin->id,
        ]);
    }

    #[Test]
    public function saving_the_same_value_does_not_record_a_change()
    {
        $this->setInductionCode();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->put(route('settings.update', 'general_induction_code'), ['setting_value' => self::INDUCTION_CODE]);

        $response->assertRedirect(route('settings.index'));
        $this->assertDatabaseHas('settings', ['key' => 'general_induction_code', 'updated_by' => null]);
    }

    #[Test]
    public function a_value_is_required()
    {
        $this->setInductionCode();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->put(route('settings.update', 'general_induction_code'), ['setting_value' => '']);

        $response->assertSessionHasErrors(['setting_value' => 'The setting value field is required.']);
        $this->assertSame(self::INDUCTION_CODE, Settings::get(SettingKey::GeneralInductionCode));
    }

    #[Test]
    public function a_value_with_characters_other_than_letters_numbers_and_hyphens_is_rejected()
    {
        $this->setInductionCode();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->put(route('settings.update', 'general_induction_code'), ['setting_value' => '<b>CODE</b>']);

        $response->assertSessionHasErrors(['setting_value' => 'Use only letters, numbers and hyphens.']);
        $this->assertSame(self::INDUCTION_CODE, Settings::get(SettingKey::GeneralInductionCode));
    }

    #[Test]
    public function an_induction_code_longer_than_32_characters_is_rejected()
    {
        $this->setInductionCode();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->put(route('settings.update', 'general_induction_code'), ['setting_value' => str_repeat('A', 33)]);

        $response->assertSessionHasErrors(['setting_value' => 'The setting value must not be greater than 32 characters.']);
        $this->assertSame(self::INDUCTION_CODE, Settings::get(SettingKey::GeneralInductionCode));
    }

    #[Test]
    public function a_rejected_value_is_not_kept_in_the_session()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('settings.index'))
            ->put(route('settings.update', 'general_induction_code'), ['setting_value' => 'ALMOST SECRET']);

        $this->assertArrayNotHasKey('setting_value', session()->getOldInput());
    }

    #[Test]
    public function an_unknown_key_is_not_found_and_nothing_is_created()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->put(route('settings.update', 'made_up_setting'), ['setting_value' => 'ANYTHING']);

        $response->assertNotFound();
        $this->assertDatabaseMissing('settings', ['key' => 'made_up_setting']);
    }

    #[Test]
    public function a_setting_row_that_is_not_a_known_setting_cannot_be_changed()
    {
        DB::table('settings')->insert(['key' => 'some_legacy_setting', 'value' => 'untouched']);
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->put(route('settings.update', 'some_legacy_setting'), ['setting_value' => 'CHANGED']);

        $response->assertNotFound();
        $this->assertDatabaseHas('settings', ['key' => 'some_legacy_setting', 'value' => 'untouched']);
    }

    /**
     * The general induction page shows the code to inducted members on purpose,
     * so this member hasn't been inducted.
     */
    #[Test]
    public function a_member_never_receives_the_induction_code_on_the_pages_they_can_reach()
    {
        $this->setInductionCode();
        $member = User::factory()->create(['induction_completed' => false]);

        $pages = [
            route('account.show', $member->id),
            route('general-induction.show'),
            route('courses.index'),
        ];

        foreach ($pages as $page) {
            $response = $this->actingAs($member)->get($page);

            $response->assertOk();
            $response->assertDontSee(self::INDUCTION_CODE);
        }
    }

    private function setInductionCode(): void
    {
        Settings::set(SettingKey::GeneralInductionCode, self::INDUCTION_CODE);
    }
}
