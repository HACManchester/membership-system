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
        $this->expectExceptionMessageIsOrContains('this is deprecated');

        trigger_error('this is deprecated', E_USER_DEPRECATED);
    }

    /**
     * Libraries such as Faker report deprecated API use through a silenced
     * trigger_error(), so the `@` mustn't hide it.
     */
    #[Test]
    public function a_silenced_deprecation_throws()
    {
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessageIsOrContains('this is quietly deprecated');

        @trigger_error('this is quietly deprecated', E_USER_DEPRECATED);
    }

    #[Test]
    public function a_deprecation_raised_inside_vendor_throws()
    {
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessageIsOrContains('vendor deprecation');

        $this->activeHandler()(E_USER_DEPRECATED, 'vendor deprecation', base_path('vendor/autoload.php'), 1);
    }

    #[Test]
    public function a_known_vendor_deprecation_does_not_fail_the_test()
    {
        $file = base_path('vendor/gocardless/gocardless-pro/lib/Client.php');

        // Would throw ErrorException if the deprecation weren't recognised as known
        $this->activeHandler()(E_DEPRECATED, 'Creation of dynamic property GoCardlessPro\Client::$x is deprecated', $file, 1);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function a_known_vendor_message_raised_elsewhere_still_throws()
    {
        $this->expectException(\ErrorException::class);

        $this->activeHandler()(E_DEPRECATED, 'Creation of dynamic property GoCardlessPro\Client::$x is deprecated', base_path('app/Helpers/GoCardlessHelper.php'), 1);
    }

    #[Test]
    public function other_errors_still_reach_laravels_handler()
    {
        // Laravel converts warnings to ErrorException itself; that must be unaffected
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessageIsOrContains('this is a warning');

        trigger_error('this is a warning', E_USER_WARNING);
    }

    /**
     * The handler Tests\TestCase installed, read without replacing it.
     */
    private function activeHandler(): callable
    {
        $handler = set_error_handler(function () {
            return true;
        });
        restore_error_handler();

        return $handler;
    }
}
