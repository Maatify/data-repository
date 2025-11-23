<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Base;

use Doctrine\DBAL\Connection;
use Maatify\Common\Contracts\Adapter\AdapterInterface;
use PDO;

abstract class BaseMySQLRepository extends BaseRepository
{

    protected function getPdoConnection(): PDO
    {
        $driver = $this->assertDriverAvailable(
            $this->adapter->getDriver(),
            'PDO'
        );

        return $this->assertDriverInstance($driver, PDO::class);
    }

    protected function getDbalConnection(): Connection
    {
        $driver = $this->assertDriverAvailable(
            $this->adapter->getDriver(),
            'Doctrine DBAL'
        );

        return $this->assertDriverInstance($driver, Connection::class);
    }
}
