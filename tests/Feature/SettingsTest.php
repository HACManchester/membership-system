<?php

namespace Tests\Feature;

use BB\Entities\Settings;
use BB\Support\SettingKey;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    #[Test]
    public function set_replaces_the_value_of_an_existing_setting()
    {
        Settings::set(SettingKey::GeneralInductionCode, 'NEW-CODE');

        $this->assertSame(['NEW-CODE'], DB::table('settings')->where('key', 'general_induction_code')->pluck('value')->all());
        $this->assertSame('NEW-CODE', Settings::get(SettingKey::GeneralInductionCode));
    }

    #[Test]
    public function set_creates_a_setting_whose_row_is_missing()
    {
        DB::table('settings')->where('key', 'general_induction_code')->delete();

        Settings::set(SettingKey::GeneralInductionCode, 'NEW-CODE');

        $this->assertDatabaseHas('settings', ['key' => 'general_induction_code', 'value' => 'NEW-CODE']);
    }

    #[Test]
    public function get_throws_when_the_row_is_missing()
    {
        DB::table('settings')->where('key', 'general_induction_code')->delete();

        $this->expectException(ModelNotFoundException::class);

        Settings::get(SettingKey::GeneralInductionCode);
    }
}
