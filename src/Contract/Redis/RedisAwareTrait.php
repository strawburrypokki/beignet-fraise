<?php

namespace App\Contract\Redis;

use Predis\Client;

trait RedisAwareTrait
{
    /**
     * Redis connection.
     *
     * @var Client
     */
    protected $redisClient;

    /**
     * Get redis connection.
     *
     * @return Client
     */ 
    public function getRedisClient()
    {
        return $this->redisClient;
    }
}
