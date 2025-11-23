<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Base;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use MongoDB\Collection;
use MongoDB\Database;

abstract class BaseMongoRepository extends BaseRepository
{

    protected function getDatabase(): Database
    {
        $driver = $this->assertDriverAvailable(
            $this->adapter->getDriver(),
            'MongoDB Database'
        );

        return $this->assertDriverInstance($driver, Database::class);
    }

    protected function getCollection(string $name): Collection
    {
        $database = $this->getDatabase();

        return $database->selectCollection($name);
    }
}
