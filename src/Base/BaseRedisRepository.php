<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Base;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Predis\Client as PredisClient;
use Redis;

abstract class BaseRedisRepository extends BaseRepository
{
    public function __construct(AdapterInterface $adapter)
    {
        parent::__construct($adapter);
    }

    protected function getRedis(): Redis
    {
        $driver = $this->assertDriverAvailable(
            $this->adapter->getDriver(),
            'Redis'
        );

        return $this->assertDriverInstance($driver, Redis::class);
    }

    protected function getPredis(): PredisClient
    {
        $driver = $this->assertDriverAvailable(
            $this->adapter->getDriver(),
            'Predis'
        );

        return $this->assertDriverInstance($driver, PredisClient::class);
    }
}
