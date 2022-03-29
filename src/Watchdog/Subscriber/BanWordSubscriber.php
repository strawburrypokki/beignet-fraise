<?php

namespace App\Watchdog\Subscriber;

use App\Contract\Redis\RedisAwareInterface;
use App\Contract\Redis\RedisAwareTrait;
use App\Watchdog\Event\SniffMessageEvent;
use App\Watchdog\Subscriber\Command\BanwordSubscriber as BanwordCommand;
use Predis\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BanWordSubscriber implements EventSubscriberInterface, RedisAwareInterface
{
    use RedisAwareTrait;

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
        // Remove spaces from $message. They have no impact on the phonetic
        $message = preg_replace("/\s+/", "", $event->getMessage()->getMessage());
        $messageMetaphoneKey = metaphone($message);
        // dump($messageMetaphoneKey);

        // Keep track of banwords already detected
        // This might improve performances if on banword is in multiple configs
        $fastcache = [];
        foreach ($this->redisClient->hgetall(BanwordCommand::REDIS_KEY) as $config) {
            $banwords = explode(',', $config);
            $find = 0;
            foreach ($banwords as $banword) {
                // If $banword has already been detected in $message
                if (isset($fastcache[$banword])) {
                    $find++;
                    continue;
                }

                $banwordMetaphoneKey = metaphone($banword);
                // dump($banwordMetaphoneKey);

                // Ban word metaphone key is in the user message
                if (strstr($messageMetaphoneKey, $banwordMetaphoneKey)) {
                    $fastcache[$banword] = true;
                    $find++;
                }
            }

            // All ban words where inside the user message
            if ($find == count($banwords)) {
                if ($event->getMessage()->hasRankModerator()) {
                    $event->setResponse('C\'est pas bien de narguer les gens!');
                } else {
                    $event->setResponse('Je reconnais ca! C\'est motif de ban! ⛔');
                }

                $event->stopPropagation();
                break;
            }
        }
    }
}
