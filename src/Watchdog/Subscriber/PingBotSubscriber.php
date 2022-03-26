<?php

namespace App\Watchdog\Subscriber;

use App\Watchdog\Event\SniffMessageEvent;
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

    protected $broadcasterReplies = [
        'Oui bonjour!',
        'Vous êtes trop fort!',
        'A votre service!',
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            SniffMessageEvent::NAME => [
                ['onPingBot', 0],
            ],
        ];
    }

    public function __construct(string $twitchBotAccount)
    {
        $this->twitchBotAccount = $twitchBotAccount;
    }

    public function onPingBot(SniffMessageEvent $event)
    {
        $event->getMessage()->parseRawMessage();
        if (stristr($event->getMessage()->getRawMessage(), $this->twitchBotAccount)) {
            $repliesPool = $event->getMessage()->isBroadcaster() ? $this->broadcasterReplies : $this->replies;
            $event->setResponse($repliesPool[array_rand($repliesPool)]);
            $event->stopPropagation();
        }
    }
}
