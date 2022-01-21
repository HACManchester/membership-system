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

        switch ($level){
            case(self::JOB):
                $emoji = "⏰";
            case(self::LOG):
                $emoji = "📜";
            case(self::RENDER):
                $emoji = "👀";
            case(self::ERROR):
                $emoji = "🛑";
            case(self::WARNING):
                $emoji = "⚠️";
        }

        return $emoji . $this->identifier ? " [" . $this->identifier . "] " : " ";
    }

    public function notify($level, $message)
    {
        (new HttpClient)->get(
            "https://api.telegram.org/bot" . env('TELEGRAM_BOT_KEY') . "/sendMessage" .
            "?chat_id=" . env('TELEGRAM_BOT_CHAT') . 
            "&text=" . $this->getId($level) . urlencode($message)
        );
    }

} 
