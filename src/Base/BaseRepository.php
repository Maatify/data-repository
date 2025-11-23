<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Base;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;

abstract class BaseRepository
{
    public function __construct(protected AdapterInterface $adapter)
    {
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * @template T of object
     * @param callable(AdapterInterface):(?T) $driverFetcher
     * @param class-string<T> $expectedClass
     * @return T
     */
    protected function resolveDriver(
        callable $driverFetcher,
        string $description,
        string $expectedClass
    ): object {
        $driver = $driverFetcher($this->adapter);

        $driver = $this->assertDriverAvailable($driver, $description);

        /** @var T $validatedDriver */
        $validatedDriver = $this->assertDriverInstance($driver, $expectedClass);

        return $validatedDriver;
    }

    /**
     * @template T of object
     * @param T|null $driver
     * @return T
     */
    protected function assertDriverAvailable(object|null $driver, string $description): object
    {
        if ($driver === null) {
            throw RepositoryException::forMissingDriver($description);
        }

        return $driver;
    }

    protected function assertAdapterInstance(AdapterInterface $adapter, string $expectedClass): void
    {
        if (!$adapter instanceof $expectedClass) {
            throw RepositoryException::forInvalidAdapter($expectedClass, $adapter);
        }
    }

    /**
     * @template T of object
     * @param object $driver
     * @param class-string<T> $expectedClass
     * @return T
     */
    protected function assertDriverInstance(object $driver, string $expectedClass): object
    {
        if (!$driver instanceof $expectedClass) {
            throw RepositoryException::forInvalidDriver($expectedClass, $driver);
        }

        /** @var T $driver */
        return $driver;
    }
}
