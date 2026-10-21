<?php

namespace Tests\Unit\Notifications;

use BB\Helpers\TelegramErrorHelper;
use BB\Notifications\ErrorNotification;
use NotificationChannels\Telegram\Telegram;
use Tests\TestCase;

/**
 * The exception-alert path, end to end apart from the network: a real
 * ErrorNotification through the real TelegramChannel, with only the outbound
 * Telegram API call mocked. HandlerTest stubs notifyTelegram() and the suite
 * runs with Telegram disabled, so without this a change in
 * laravel-notification-channels/telegram could break alerts silently.
 */
class ErrorNotificationTest extends TestCase
{
    /** @test */
    public function an_error_alert_is_sent_to_the_configured_chat_as_html_with_a_logs_button()
    {
        config([
            'services.telegram-bot-api.enabled' => true,
            'telegram.bot_chat' => '-100123456',
        ]);

        $sent = null;
        $telegram = \Mockery::mock(Telegram::class);
        $telegram->shouldReceive('sendMessage')->once()->andReturnUsing(function (array $params) use (&$sent) {
            $sent = $params;

            return new \GuzzleHttp\Psr7\Response(200, [], '{"ok":true,"result":{}}');
        });
        $this->app->instance(Telegram::class, $telegram);

        $exception = new \RuntimeException('Something broke <badly>');

        (new TelegramErrorHelper())->notify(new ErrorNotification('error', 'Error (x10)', false, $exception));

        $this->assertSame('-100123456', (string) $sent['chat_id']);
        $this->assertSame('HTML', $sent['parse_mode']);
        $this->assertStringContainsString('<b>Error (x10)</b>', $sent['text']);
        $this->assertStringContainsString('Something broke', $sent['text']);
        $this->assertStringContainsString('A guest', $sent['text']);

        $markup = json_decode($sent['reply_markup'], true);
        $button = $markup['inline_keyboard'][0][0];
        $this->assertSame('View logs', $button['text']);
        $this->assertSame(route('logs'), $button['url']);
    }

    /** @test */
    public function nothing_is_sent_when_telegram_is_disabled()
    {
        config(['services.telegram-bot-api.enabled' => false]);

        $telegram = \Mockery::mock(Telegram::class);
        $telegram->shouldNotReceive('sendMessage');
        $this->app->instance(Telegram::class, $telegram);

        (new TelegramErrorHelper())->notify(new ErrorNotification('error', 'Error', false, new \RuntimeException('x')));

        $this->addToAssertionCount(1);
    }
}
