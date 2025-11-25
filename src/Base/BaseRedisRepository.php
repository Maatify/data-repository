<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 01:05:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Base;

use Maatify\DataRepository\Exceptions\RepositoryException;
use Redis;

abstract class BaseRedisRepository extends BaseRepository
{
    protected function validateAdapter(): void
    {
        /** @var mixed $driver */
        $driver = $this->adapter->getDriver();

        $isRedis = $driver instanceof Redis;
        $isPredis = (is_object($driver) && str_contains(get_class($driver), 'Predis\Client'));
        $isFake = str_contains(get_class($this->adapter), 'FakeRedisAdapter');

        if (! $isRedis && ! $isPredis && ! $isFake) {
            throw RepositoryException::driverNotSupported(get_class($this->adapter));
        }
    }
}
