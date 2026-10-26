<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Symfony Mailer (Laravel 9+) refuses to send any email without a From
 * address. SwiftMailer didn't, so a MAIL_FROM_ADDRESS of "null" (as
 * .env.example used to have) or empty would silently have become fatal for
 * every email. config/mail.php falls back to the default instead.
 */
class MailFromAddressTest extends TestCase
{
    #[DataProvider('unsetValues')]
    #[Test]
    public function the_from_address_falls_back_when_the_env_value_is_null_or_empty($value)
    {
        $original = getenv('MAIL_FROM_ADDRESS');
        $this->setEnv($value);

        try {
            $config = require base_path('config/mail.php');
        } finally {
            $this->setEnv($original === false ? null : $original);
        }

        $this->assertSame('alerts@hacman.org.uk', $config['from']['address']);
    }

    public static function unsetValues()
    {
        return ['"null"' => ['null'], 'empty' => [''], 'missing' => [null]];
    }

    #[Test]
    public function a_configured_from_address_is_used()
    {
        $original = getenv('MAIL_FROM_ADDRESS');
        $this->setEnv('membership@example.test');

        try {
            $config = require base_path('config/mail.php');
        } finally {
            $this->setEnv($original === false ? null : $original);
        }

        $this->assertSame('membership@example.test', $config['from']['address']);
    }

    private function setEnv($value)
    {
        if ($value === null) {
            putenv('MAIL_FROM_ADDRESS');
            unset($_ENV['MAIL_FROM_ADDRESS'], $_SERVER['MAIL_FROM_ADDRESS']);
        } else {
            putenv("MAIL_FROM_ADDRESS={$value}");
            $_ENV['MAIL_FROM_ADDRESS'] = $_SERVER['MAIL_FROM_ADDRESS'] = $value;
        }
    }
}
