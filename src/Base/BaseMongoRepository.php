<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 01:08
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Base;

use Maatify\DataRepository\Exceptions\RepositoryException;
use MongoDB\Client;
use MongoDB\Database;

abstract class BaseMongoRepository extends BaseRepository
{
    protected string $databaseName = '';

    protected function validateAdapter(): void
    {
        /** @var mixed $driver */
        $driver = $this->adapter->getDriver();

        $isMongo = $driver instanceof Client || $driver instanceof Database;
        $isFake = str_contains(get_class($this->adapter), 'FakeMongoAdapter');

        if (! $isMongo && ! $isFake) {
            throw RepositoryException::driverNotSupported(get_class($this->adapter));
        }
    }

    protected function getCollection(string $collectionName): mixed
    {
        /** @var mixed $driver */
        $driver = $this->getDriver();

        if ($driver instanceof Client) {
            // Client requires database name and collection name
            return $driver->selectCollection($this->databaseName, $collectionName);
        }

        if ($driver instanceof Database) {
            // Database object already selected the DB, just needs a collection
            return $driver->selectCollection($collectionName);
        }

        // Fallback for Fakes/Mocks (Duck Typing)
        // If validation passed (it's a Fake), we allow method call if it exists.
        if (is_object($driver) && method_exists($driver, 'selectCollection')) {
            return $driver->selectCollection($collectionName);
        }

        return null;
    }
}
