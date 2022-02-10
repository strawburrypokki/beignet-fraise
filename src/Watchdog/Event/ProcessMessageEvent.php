<?php

namespace App\Watchdog\Event;

use App\Watchdog\Message;
use Symfony\Contracts\EventDispatcher\Event;

class ProcessMessageEvent extends Event
{
    public const NAME = 'message.process';

    protected $message;

    protected $response;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * Get the value of response
     */ 
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the value of response
     *
     * @return  self
     */ 
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }
}
