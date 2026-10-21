<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Guards the rule in Tests\TestCase: a PHP deprecation must fail the test that
 * triggers it, because Laravel itself now swallows them under PHPUnit.
 */
class DeprecationsFailTestsTest extends TestCase
{
    /** @test */
    public function a_deprecation_raised_during_a_test_throws()
    {
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('this is deprecated');

        trigger_error('this is deprecated', E_USER_DEPRECATED);
    }

    /** @test */
    public function other_errors_still_reach_laravels_handler()
    {
        // Laravel converts warnings to ErrorException itself; that must be unaffected
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('this is a warning');

        trigger_error('this is a warning', E_USER_WARNING);
    }
}
