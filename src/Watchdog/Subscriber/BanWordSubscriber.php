<?php

namespace App\Watchdog\Subscriber;

use App\Contract\Redis\RedisAwareInterface;
use App\Contract\Redis\RedisAwareTrait;
use App\Watchdog\Event\SniffMessageEvent;
use Predis\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BanWordSubscriber implements EventSubscriberInterface, RedisAwareInterface
{
    use RedisAwareTrait;

    protected $banWordConfigs = [
        // 'demon',
        // 'slayer',
        // 'meurt',
        'demon,slayer',
        // 'demon,slayer,meurt',
    ];

    public function __construct(Client $client)
    {
        $this->redisClient = $client;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SniffMessageEvent::NAME => [
                ['onBanWord', 999],
            ],
        ];
    }

    public function onBanWord(SniffMessageEvent $event)
    {
        $message = preg_replace("/\s+/", "", $event->getMessage()->getMessage());
        $messageMetaphoneKey = metaphone($message);
        // dump($messageMetaphoneKey);
        foreach($this->banWordConfigs as $config) {
            $banwords = explode(',', $config);
            $find = 0;
            foreach($banwords as $banword) {
                $banwordMetaphoneKey = metaphone($banword);
                // dump($banwordMetaphoneKey);

                // Ban word metaphone key is in the user message
                if (strstr($messageMetaphoneKey, $banwordMetaphoneKey)) {
                    $find++;
                }
            }

            // All ban words where inside the user message
            if($find == count($banwords)) {
                if($event->getMessage()->hasRankModerator()) {
                    $event->setResponse('C\'est pas bien de narguer les gens!');
                } else {
                    $event->setResponse('Je reconnais ca! C\'est motif de ban!');
                }
                
                $event->stopPropagation();
                break;
            }
        }
    }
}
