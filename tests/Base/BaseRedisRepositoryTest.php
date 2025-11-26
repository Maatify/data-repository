<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Base;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Base\BaseRedisRepository;
use Maatify\DataRepository\Exceptions\RepositoryException;
use PHPUnit\Framework\TestCase;
use Predis\Client as PredisClient;
use Redis;

class BaseRedisRepositoryTest extends TestCase
{
    public function testValidateAdapterAcceptsPredisClient(): void
    {
        $adapter = new RedisAdapterStub(new \Predis\Client());
        $repository = new RedisRepositoryStub($adapter);

        $this->assertTrue($repository->delete(1));
    }

    public function testValidateAdapterAcceptsFakeAdapterName(): void
    {
        $adapter = new FakeRedisAdapter(new \Predis\Client());
        $repository = new RedisRepositoryStub($adapter);

        $this->assertTrue($repository->delete(1));
    }

    public function testValidateAdapterRejectsUnsupportedDriver(): void
    {
        /** @var AdapterInterface&\PHPUnit\Framework\MockObject\MockObject $adapter */
        $adapter = $this->createMock(AdapterInterface::class);

        $adapter->method('getDriver')->willReturn(new \stdClass());
        $adapter->method('getConnection')->willReturn(new \stdClass());

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage(
            RepositoryException::driverNotSupported($adapter::class)->getMessage()
        );

        new RedisRepositoryStub($adapter, false);
    }

}

class RedisRepositoryStub extends BaseRedisRepository
{
    public function __construct(AdapterInterface $adapter, bool $skipValidation = true)
    {
        if ($skipValidation) {
            $this->adapter = $adapter;

            return;
        }

        parent::__construct($adapter);
    }

    public function find(int|string $id): ?array
    {
        return null;
    }

    /**
     * @param   array<string, mixed>        $filters
     * @param   array<string, string>|null  $orderBy
     *
     * @return array<int, array<string, mixed>>
     */
    public function findBy(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return [];
    }

    /**
     * @param   array<string, mixed>  $filters
     *
     * @return array<string, mixed>|null
     */
    public function findOneBy(array $filters): ?array
    {
        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAll(): array
    {
        return [];
    }

    /**
     * @param   array<string, mixed>  $filters
     */
    public function count(array $filters = []): int
    {
        return 0;
    }

    /**
     * @param   array<string, mixed>  $data
     *
     * @return int|string
     */
    public function insert(array $data): int|string
    {
        return 1;
    }

    /**
     * @param   array<string, mixed>  $data
     */
    public function update(int|string $id, array $data): bool
    {
        return true;
    }

    public function delete(int|string $id): bool
    {
        return true;
    }
}

class RedisAdapterStub implements AdapterInterface
{
    public function __construct(private PredisClient|Redis $driver)
    {
    }

    public function getDriver(): PredisClient|Redis
    {
        return $this->driver;
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

    public function getConnection(): PredisClient|Redis
    {
        return $this->driver;
    }

    public function debugConfig(): object
    {
        return (object) ['driver' => $this->driver::class];
    }

    public function healthCheck(): bool
    {
        return true;
    }
}

class FakeRedisAdapter extends RedisAdapterStub
{
}

namespace Predis;

if (! class_exists(Client::class)) {
    class Client
    {
    }
}
