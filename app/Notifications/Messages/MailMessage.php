<?php

namespace BB\Notifications\Messages;

use Illuminate\Notifications\Action;
use Illuminate\Notifications\Messages\MailMessage as IlluminateMailMessage;

class MailMessage extends IlluminateMailMessage
{
    /**
     * The "action" lines of the notification.
     *
     * @var array
     */
    public $actionLines = [];

    /**
     * Add a line of text to the notification.
     *
     * @param  \Illuminate\Notifications\Action|string|array  $line
     * @return $this
     */
    public function with($line)
    {
        if ($line instanceof Action) {
            $this->actionLines[] = $line;
            return $this;
        }
        
        return parent::with($line);
    }
    /**
     * Configure the "call to action" button.
     *
     * @param  string  $text
     * @param  string  $url
     * @return $this
     */
    public function action($text, $url)
    {
        $this->with(new Action($text, $url));

        return $this;
    }

    /**
     * Get an array representation of the message.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'level' => $this->level,
            'subject' => $this->subject,
            'introLines' => $this->introLines,
            'outroLines' => $this->outroLines,
            'actionLines' => $this->actionLines,
            'actionText' => $this->actionText,
            'actionUrl' => $this->actionUrl,
        ];
    }
}
