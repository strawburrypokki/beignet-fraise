<?php

namespace App\Watchdog;

use App\Watchdog\Event\ProcessMessageEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MessageProcessor
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
    public function process(string $rawMessage)
    {
        $message = new Message($rawMessage);
        $event = new ProcessMessageEvent($message);
        $this->dispatcher->dispatch($event, ProcessMessageEvent::NAME);

        return $event->getResponse();
    }
}
