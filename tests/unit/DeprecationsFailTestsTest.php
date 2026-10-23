<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Guards the rule in Tests\TestCase: a PHP deprecation must fail the test that
 * triggers it, because Laravel itself now swallows them under PHPUnit.
 */
class DeprecationsFailTestsTest extends TestCase
{
    #[Test]
    public function a_deprecation_raised_during_a_test_throws()
    {
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('this is deprecated');

        trigger_error('this is deprecated', E_USER_DEPRECATED);
    }

    /**
     * Deprecations inside dependencies aren't ours to fix — e.g. Laravel 8's own
     * Timebox on PHP 8.1 — so they're handed to Laravel, not failed.
     */
    #[Test]
    public function a_deprecation_raised_inside_vendor_does_not_fail_the_test()
    {
        // Read the active handler (the one Tests\TestCase installed) without replacing it
        $handler = set_error_handler(function () {
            return true;
        });
        restore_error_handler();

        // Would throw ErrorException if the deprecation were treated as ours
        $handler(E_USER_DEPRECATED, 'vendor deprecation', base_path('vendor/autoload.php'), 1);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function other_errors_still_reach_laravels_handler()
    {
        // Laravel converts warnings to ErrorException itself; that must be unaffected
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('this is a warning');

        trigger_error('this is a warning', E_USER_WARNING);
    }
}
