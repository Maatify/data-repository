<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 01:07
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Base;

use Doctrine\DBAL\Connection;
use Maatify\DataRepository\Exceptions\RepositoryException;
use PDO;

/**
 * Base Repository for MySQL interactions.
 * Supports both PDO and Doctrine DBAL drivers via normalization.
 *
 * @template T of object
 * @extends BaseRepository<T>
 */
abstract class BaseMySQLRepository extends BaseRepository
{
    protected function validateAdapter(): void
    {
        /** @var mixed $driver */
        $driver = $this->adapter->getDriver();

        $isPdo = $driver instanceof PDO;
        // removed is_object check to prevent redundancy warnings, assuming a mixed driver
        $isDbal = $driver instanceof Connection
                  || (is_object($driver) && str_contains(get_class($driver), 'Doctrine\DBAL\Connection'));

        $isFake = str_contains(get_class($this->adapter), 'FakeMySQLAdapter')
                  || str_contains(get_class($this->adapter), 'FakeDBALAdapter');

        if (! $isPdo && ! $isDbal && ! $isFake) {
            throw RepositoryException::driverNotSupported(get_class($this->adapter));
        }
    }
}
