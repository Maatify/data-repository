<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Base;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Base\BaseMongoRepository;
use Maatify\DataRepository\Exceptions\RepositoryException;
use PDO;
use PHPUnit\Framework\TestCase;
use MongoDB\Client as MongoClient;
use MongoDB\Database as MongoDatabase;

class BaseMongoRepositoryTest extends TestCase
{
    public function testValidateAdapterAcceptsFakeMongoAdapter(): void
    {
        /** @var \MongoDB\Database&\PHPUnit\Framework\MockObject\MockObject $db */
        $db = $this->getMockBuilder(\MongoDB\Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['selectCollection'])
            ->getMock();

        /** @var \MongoDB\Collection&\PHPUnit\Framework\MockObject\MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(\MongoDB\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $db->method('selectCollection')->willReturn($collectionMock);

        /** @var \MongoDB\Client&\PHPUnit\Framework\MockObject\MockObject $client */
        $client = $this->getMockBuilder(\MongoDB\Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adapter = new FakeMongoAdapter($db, $client);

        $repository = new MongoRepositoryStub($adapter);
        $result = $repository->fetchCollection('logs');

        $this->assertInstanceOf(\MongoDB\Collection::class, $result);
        $this->assertSame($collectionMock, $result);
    }

    public function testValidateAdapterRejectsUnsupportedDrivers(): void
    {
        $adapter = new MongoInvalidAdapter(new \PDO('sqlite::memory:'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage(
            RepositoryException::driverNotSupported(MongoInvalidAdapter::class)->getMessage()
        );

        new MongoRepositoryStub($adapter);
    }

    public function testGetCollectionSupportsMongoClient(): void
    {
        $collection = $this->getMockBuilder(\MongoDB\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockBuilder(\MongoDB\Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['selectCollection'])
            ->getMock();
        $client->expects($this->once())
            ->method('selectCollection')
            ->with('testing', 'client_collection')
            ->willReturn($collection);

        $adapter = new MongoClientAdapterStub($client);

        $repository = new MongoRepositoryStub($adapter);

        $result = $repository->fetchCollection('client_collection');

        $this->assertSame($collection, $result);
    }

    public function testGetCollectionReturnsNullForUnrecognizedDriver(): void
    {
        $adapter = new class () extends FakeMongoAdapter {
            public function __construct()
            {
            }

            /**
             * @return \Doctrine\DBAL\Connection|\MongoDB\Database|PDO|\Predis\Client|\Redis|object
             */
            public function getDriver(): object
            {
                // Return a PDO driver which is allowed by AdapterInterface but
                // intentionally unsupported by BaseMongoRepository, causing
                // getCollection() to fall back to null.
                return new PDO('sqlite::memory:');
            }
        };

        $repository = new MongoRepositoryStub($adapter);

        $this->assertNull($repository->fetchCollection('missing'));
    }

    public function testGetCollectionSupportsDuckTypingFakeDriver(): void
    {
        // Fake driver with selectCollection() but not MongoDatabase/MongoClient
        $fakeDriver = new class () {
            public function selectCollection(string $collectionName): string
            {
                return 'duck-' . $collectionName;
            }
        };

        // Adapter stub that pretends to be FakeMongoAdapter
        $adapter = new class ($fakeDriver) extends FakeMongoAdapter {
            public function __construct(private object $driver)
            {
            }

            /**
             * @return \MongoDB\Database|\MongoDB\Client|object
             */
            public function getDriver(): mixed
            {
                return $this->driver;
            }
        };

        $repository = new MongoRepositoryStub($adapter);

        $result = $repository->fetchCollection('logs');

        $this->assertSame('duck-logs', $result);
    }

}

class MongoRepositoryStub extends BaseMongoRepository
{
    protected string $databaseName = 'testing';

    public function __construct(AdapterInterface $adapter)
    {
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
        return 'mongo-id';
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

    public function fetchCollection(string $name): mixed
    {
        return $this->getCollection($name);
    }
}

class FakeMongoAdapter implements AdapterInterface
{
    public function __construct(
        private MongoDatabase $db,
        private MongoClient $client,
    ) {
    }

    public function getDriver(): mixed
    {
        return $this->db;
    }

    public function getConnection(): MongoClient
    {
        return $this->client;
    }

    public function getType(): string
    {
        return 'mongo';
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
    public function debugConfig(): object
    {
        return (object) ['driver' => 'fake'];
    }
    public function healthCheck(): bool
    {
        return true;
    }
}

class MongoDriverStub
{
    public function selectCollection(string $collectionName): string
    {
        return $collectionName;
    }
}

class MongoClientAdapterStub implements AdapterInterface
{
    public function __construct(private MongoClient $client)
    {
    }

    /**
     * @return MongoClient|MongoDatabase|PDO|\Doctrine\DBAL\Connection|\Predis\Client|\Redis
     */
    public function getDriver(): MongoClient|MongoDatabase|PDO|\Doctrine\DBAL\Connection|\Predis\Client|\Redis
    {
        // Return the client itself; BaseMongoRepository will treat this as
        // MongoDB\Client and call selectCollection() accordingly.
        return $this->client;
    }

    public function getType(): string
    {
        return 'mongo-client';
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

    public function getConnection(): MongoClient
    {
        return $this->client;
    }

    public function debugConfig(): object
    {
        return (object) ['driver' => 'client'];
    }

    public function healthCheck(): bool
    {
        return true;
    }
}

class MongoInvalidAdapter implements AdapterInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getDriver(): PDO
    {
        return $this->pdo;
    }

    public function getType(): string
    {
        return 'invalid';
    }

    public function connect(): void
    {
    }

    public function isConnected(): bool
    {
        return false;
    }

    public function disconnect(): void
    {
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function debugConfig(): object
    {
        return (object) [];
    }

    public function healthCheck(): bool
    {
        return false;
    }
}
