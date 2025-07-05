<?php

namespace Tests\Unit;

use BB\Entities\Course;
use BB\Entities\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_is_preview_by_default()
    {
        // No setting exists, should be in preview mode
        $this->assertTrue(Course::isPreview());
        $this->assertFalse(Course::isLive());
    }

    public function test_course_is_preview_when_setting_is_false()
    {
        Settings::create(['key' => 'inductions_live', 'value' => 'false']);
        
        $this->assertTrue(Course::isPreview());
        $this->assertFalse(Course::isLive());
    }

    public function test_course_is_live_when_setting_is_true()
    {
        Settings::create(['key' => 'inductions_live', 'value' => 'true']);
        
        $this->assertFalse(Course::isPreview());
        $this->assertTrue(Course::isLive());
    }

    public function test_course_is_preview_when_setting_has_any_other_value()
    {
        Settings::create(['key' => 'inductions_live', 'value' => 'maybe']);
        
        $this->assertTrue(Course::isPreview());
        $this->assertFalse(Course::isLive());
    }

    public function test_course_is_preview_when_setting_is_empty_string()
    {
        Settings::create(['key' => 'inductions_live', 'value' => '']);
        
        $this->assertTrue(Course::isPreview());
        $this->assertFalse(Course::isLive());
    }
}