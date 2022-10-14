<?php namespace BB\Helpers;

use GuzzleHttp\Client as HttpClient;

class TelegramHelper
{
    const JOB = 1;
    const LOG = 2;
    const RENDER = 3;
    const ERROR = 4;
    const WARNING = 5;

    private $identifier = '';

    public function __construct($id)
    {
        $this->identifier = $id;
    }

    private function getId($level){
        $emoji = "ℹ️";

        switch ($level) { 
            case(self::JOB):
                $emoji = "⏰";
                break;
            case(self::LOG):
                $emoji = "📜";
                break;
            case(self::RENDER):
                $emoji = "👀";
                break;
            case(self::ERROR):
                $emoji = "🛑";
                break;
            case(self::WARNING):
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
        
        // TODO: Replace with Notifications pushing to a Telegram notification channel?
        (new HttpClient)->get(
            "https://api.telegram.org/bot{$botKey}/sendMessage" .
            "?parse_mode=HTML&chat_id=${botChat}". 
            "&text=" . $this->getId($level) . urlencode($message)
        );
    }

} 
