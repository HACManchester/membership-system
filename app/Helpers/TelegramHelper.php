<?php

namespace BB\Helpers;

use GuzzleHttp\Client;

class TelegramHelper
{
    const JOB = 1;
    const LOG = 2;
    const RENDER = 3;
    const ERROR = 4;
    const WARNING = 5;

    private $identifier = '';

    /** @var Client */
    protected $client;

    public function __construct($id, Client $client = null)
    {
        $this->identifier = $id;
        $this->client = $client ?? new Client();
    }

    private function getId($level)
    {
        $emoji = "ℹ️";

        switch ($level) {
            case (self::JOB):
                $emoji = "⏰";
                break;
            case (self::LOG):
                $emoji = "📜";
                break;
            case (self::RENDER):
                $emoji = "👀";
                break;
            case (self::ERROR):
                $emoji = "🛑";
                break;
            case (self::WARNING):
                $emoji = "⚠️";
                break;
        }

        $id = $this->identifier ? " [" . $this->identifier . "] " : "";
        return urlencode("$emoji $id ");
    }

    public function notify($level, $message)
    {
        $botKey = config('telegram.bot_key');
        $botChat = config('telegram.bot_chat');

        if (empty($botKey) || empty($botChat)) {
            return;
        }

        $formattedMessage = $this->getId($level) . $message;

        \Log::info("Telegram message: {$formattedMessage}");

        // TODO: Replace with Notifications pushing to a Telegram notification channel?
        try {
            $this->client->get(
                "https://api.telegram.org/bot{$botKey}/sendMessage" .
                    "?parse_mode=HTML&chat_id=${botChat}" .
                    "&text=" . urlencode($formattedMessage)
            );
        } catch (\Exception $e) {
            \Log::error("Failed to send Telegram message: {$e->getMessage()}");
        }
    }
}
