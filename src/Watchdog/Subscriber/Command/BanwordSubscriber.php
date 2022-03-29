<?php

namespace App\Watchdog\Subscriber\Command;

use App\Contract\Redis\RedisAwareInterface;
use App\Contract\Redis\RedisAwareTrait;
use App\Watchdog\Event\SniffCommandEvent;
use Predis\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BanwordSubscriber extends AbstractCommandSubscriber implements EventSubscriberInterface, RedisAwareInterface
{
    use RedisAwareTrait;

    public const ADD_ACTION_NAME = 'add';
    public const DELETE_ACTION_NAME = 'delete';
    public const CLEAR_ACTION_NAME = 'clear';
    public const LIST_ACTION_NAME = 'list';

    public const REDIS_KEY = 'banword.list';

    public function __construct(Client $client)
    {
        $this->redisClient = $client;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SniffCommandEvent::NAME => [
                ['onBanWord', 0],
            ],
        ];
    }

    public function getCommandName()
    {
        return 'banword';
    }

    public function onBanWord(SniffCommandEvent $event)
    {
        $request = trim($event->getMessage()->getMessage());
        // Does not support requested command.
        // Do nothing
        if (!$this->supports($request)) {
            return;
        }

        // Support requested command. But not allow to call
        // Do nothing and stop event propagation.
        if (!$event->getMessage()->hasRankModerator()) {
            return;
        }

        list(, $action, $name, $value) = array_merge(
            explode(' ', $request),
            [null, null, null, null]
        );

        switch ($action) {
            case self::LIST_ACTION_NAME:
                $part = [];
                foreach ($this->redisClient->hgetall(self::REDIS_KEY) as $configName => $configValue) {
                    $part[] = sprintf('%s: "%s"', $configName, $configValue);
                }

                if (empty($part)) {
                    $response = 'Aucune liste de banword configurée 🗒️';
                } else {
                    $response = implode(' || ', $part);
                }
                break;
            case self::ADD_ACTION_NAME:
                if (!strlen($name)) {
                    $response = sprintf('"name" is missing.', $name, $value);
                } elseif (!strlen($value)) {
                    $response = sprintf('"value" is missing.', $name, $value);
                } else {
                    $this->redisClient->hmset(self::REDIS_KEY, [$name => $value]);
                    $response = sprintf('%s: "%s" a été ajouté 📝', $name, $value);
                }
                break;
            case self::DELETE_ACTION_NAME:
                if (!strlen($name)) {
                    $response = sprintf('"name" is missing.', $name, $value);
                } else {
                    $this->redisClient->hdel(self::REDIS_KEY, $name);
                    $response = sprintf('"%s" a été supprimé 🗑️', $name);
                }
                break;
            case self::CLEAR_ACTION_NAME:
                $this->redisClient->del(self::REDIS_KEY);
                $response = 'La liste de banword a été vidée 🧹';
                break;
            default:
                $response = 'Je ne reconnais pas cette action :|';
                break;
        }

        // Tagging author of the command to notify about result
        $response = '@' . $event->getMessage()->getNickname() . ' -> ' . $response;

        $event->setResponse($response);
        $event->stopPropagation();
    }
}
