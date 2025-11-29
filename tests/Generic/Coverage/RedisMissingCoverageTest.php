<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 09:30
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Coverage;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class RedisMissingCoverageTest extends TestCase
{
    private object $redisMock;
    private GenericRedisRepository $repo;

    protected function setUp(): void
    {
        $this->redisMock = $this->createMock(Client::class);

        $adapter = new FakeRedisAdapterSatisfying($this->redisMock);

        $this->repo = new class ($adapter) extends GenericRedisRepository {
            protected string $keyPrefix = 'test:';
        };
    }

    public function testFindByThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('findBy() is not supported in GenericRedisRepository');
        $this->repo->findBy(['id' => 1]);
    }

    public function testFindOneByThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('findOneBy() is not supported in GenericRedisRepository');
        $this->repo->findOneBy(['id' => 1]);
    }

    public function testCountWithFiltersThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Filtering count is not supported in Redis');
        $this->repo->count(['id' => 1]);
    }
}

class FakeRedisAdapterSatisfying implements AdapterInterface
{
    public function __construct(private object $driver)
    {
    }

    /** @return mixed */
    public function getDriver(): mixed
    {
        return $this->driver;
    }

    public function getType(): string
    {
        return 'redis';
    }

    public function connect(): void
    {
    }
    public function isConnected(): bool
    {
        return true;
    }
    public function disconnect(): void
    {
    }
    /** @return mixed */
    public function getConnection(): mixed
    {
        return $this->driver;
    }
    public function debugConfig(): object
    {
        return (object)[];
    }
    public function healthCheck(): bool
    {
        return true;
    }
}
