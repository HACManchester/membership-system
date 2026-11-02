<?php

namespace Tests\Unit;

use BB\Entities\ProfileData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileDataSkillsTest extends TestCase
{
    #[Test]
    public function skills_are_stored_as_json_and_read_back_as_a_list()
    {
        $profile = ProfileData::factory()->create();

        $profile->skills = ['welding', 'laser-cutting'];
        $profile->save();

        $this->assertDatabaseHas('profile_data', ['id' => $profile->id, 'skills_array' => '["welding","laser-cutting"]']);
        $this->assertSame(['welding', 'laser-cutting'], $profile->fresh()->skills);
    }

    #[Test]
    public function a_single_skill_is_stored_as_a_list_of_one()
    {
        $profile = ProfileData::factory()->create();

        $profile->skills = 'welding';
        $profile->save();

        $this->assertDatabaseHas('profile_data', ['id' => $profile->id, 'skills_array' => '["welding"]']);
    }

    #[Test]
    public function a_profile_without_skills_reads_as_an_empty_list()
    {
        $profile = ProfileData::factory()->create(['skills_array' => null]);

        $this->assertSame([], $profile->fresh()->skills);
    }
}
