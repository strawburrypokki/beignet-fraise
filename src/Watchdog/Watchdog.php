<?php

namespace App\Watchdog;

use App\Twitch\Message;
use App\Watchdog\Event\SniffCommandEvent;
use App\Watchdog\Event\SniffMessageEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Watchdog
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Process an incoming message and determines what action it needs.
     *
     * @param string $rawMessage The RAW incoming message to process
     */
    public function sniff(string $rawMessage, LoggerInterface $logger = null)
    {
        $message = (new Message($rawMessage))->parseRawMessage();

        $event = null;
        if($message->isCommand()) {
            $event = new SniffCommandEvent($message);
        } else {
            $event = new SniffMessageEvent($message);
        }
        $this->dispatcher->dispatch($event, $event::NAME);

        return $event->getResponse();
    }
}
