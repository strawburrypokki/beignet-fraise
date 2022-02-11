<?php

namespace App\Watchdog\Subscriber;

use App\Watchdog\Event\ProcessMessageEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BanWordSubscriber implements EventSubscriberInterface
{
    protected $banWordConfigs = [
        // 'demon',
        // 'slayer',
        // 'meurt',
        'demon,slayer',
        // 'demon,slayer,meurt',
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            ProcessMessageEvent::NAME => [
                ['onBanWord', 999],
            ],
        ];
    }

    public function onBanWord(ProcessMessageEvent $event)
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
                $event->setResponse('Je reconnais ca! C\'est motif de ban!');
                $event->stopPropagation();
                break;
            }
        }
    }
}
