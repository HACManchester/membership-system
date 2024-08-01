<?php

namespace BB\FlashNotification;

use Illuminate\Session\Store;
use Illuminate\Support\MessageBag;

class FlashNotificationManager
{
    /**
     * @var Store
     */
    private $session;

    public function __construct(Store $session)
    {
        $this->session = $session;
    }

    /**
     * Flash an information message.
     * 
     * @param string     $message
     */
    public function info($message, MessageBag $details = null)
    {
        $this->message($message, $details, 'info');

        return $this;
    }

    /**
     * Flash a success message.
     *
     * @param  string    $message
     */
    public function success($message, MessageBag $details = null)
    {
        $this->message($message, $details, 'success');

        return $this;
    }

    /**
     * Flash an error message.
     *
     * @param  string    $message
     */
    public function error($message, MessageBag $details = null)
    {
        $this->message($message, $details, 'danger');

        return $this;
    }

    /**
     * Flash a warning message.
     *
     * @param  string    $message
     */
    public function warning($message, MessageBag $details = null)
    {
        $this->message($message, $details, 'warning');

        return $this;
    }


    /**
     * Flash a general message.
     *
     * @param  string    $message
     */
    public function message($message, MessageBag $details = null, $level = 'info')
    {
        $this->session->flash('flash_notification.message', $message);
        $this->session->flash('flash_notification.details', $details);
        $this->session->flash('flash_notification.level', $level);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasMessage()
    {
        return $this->session->has('flash_notification.message');
    }

    /**
     * @param string      $detail
     * @param null|string $response
     * @return bool|null
     */
    public function hasErrorDetail($detail, $response = null)
    {
        $details = $this->session->get('flash_notification.details');
        if ($details && $details->has($detail)) {
            if ($response) {
                return $response;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasDetails()
    {
        return !$this->getDetails()->isEmpty();
    }

    /**
     * @return MessageBag
     */
    public function getDetails()
    {
        $details = $this->session->get('flash_notification.details');
        if (!($details instanceof MessageBag)) {
            return new MessageBag();
        }
        return $details;
    }

    /**
     * @param string $detail
     * @param string $responseFormat
     * @return mixed
     */
    public function getErrorDetail($detail, $responseFormat = '<span class="help-block">:message</span>')
    {
        $details = $this->session->get('flash_notification.details');
        if ($this->hasErrorDetail($detail)) {
            return $details->first($detail, $responseFormat);
        }
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->session->get('flash_notification.message');
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->session->get('flash_notification.level');
    }
}
