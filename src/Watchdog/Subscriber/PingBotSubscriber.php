<?php

namespace App\Watchdog\Subscriber;

use App\Watchdog\Event\ProcessMessageEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PingBotSubscriber implements EventSubscriberInterface
{
    protected $twitchBotAccount;

    public static function getSubscribedEvents():array
    {
        return [
            ProcessMessageEvent::NAME => 'onPingBot',
        ];
    }

    public function __construct(string $twitchBotAccount)
    {
        $this->twitchBotAccount = $twitchBotAccount;
    }

    public function onPingBot(ProcessMessageEvent $event)
    {
        if (strstr($event->getMessage()->getRawMessage(), '@'.$this->twitchBotAccount)) {
            $event->setResponse("Qu'est-ce que vous me voulez vous?");
            $event->stopPropagation();
        }
    }
}
