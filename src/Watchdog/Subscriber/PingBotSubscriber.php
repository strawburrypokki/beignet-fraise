<?php

namespace App\Watchdog\Subscriber;

use App\Watchdog\Event\ProcessMessageEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PingBotSubscriber implements EventSubscriberInterface
{
    protected $twitchBotAccount;

    protected $replies = [
        'Oui?',
        'Occupé!',
        'Je passe sous un tunnel! Je vous reviens.',
        'Qu\'est-ce que vous me voulez vous?',
        'Laissez moi tranquille!',
        'C\'est pas moi, je vous jure!',
        'C\'est pas moi, c\'est lui!',
        'C\'était mérité aussi!',
        'Sortez de ma chambre!',
        '/me *beep boop beep* MrDestructoid',
        'D:',
    ];

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
            $event->setResponse($this->replies[array_rand($this->replies)]);
            $event->stopPropagation();
        }
    }
}
