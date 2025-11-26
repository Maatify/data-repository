<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Base;

use Doctrine\DBAL\Connection as DoctrineDbalConnection;
use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Base\BaseMySQLRepository;
use Maatify\DataRepository\Exceptions\RepositoryException;
use PDO;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use MongoDB\Client as MongoClient;
use MongoDB\Database as MongoDatabase;
use Predis\Client as PredisClient;
use Redis;

class BaseMySQLRepositoryTest extends TestCase
{
    public function testValidateAdapterAcceptsPdoDriver(): void
    {
        $adapter = new RecordingMySQLAdapter(new PDO('sqlite::memory:'));
        $repository = new MySQLRepositoryStub($adapter);

        $this->assertSame('items', $repository->getTableName());
    }

    public function testValidateAdapterAcceptsDoctrineDbalDriver(): void
    {
        /** @var DoctrineDbalConnection&MockObject $driver */
        $driver = $this->getMockBuilder(DoctrineDbalConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adapter = new RecordingMySQLAdapter($driver);
        $repository = new MySQLRepositoryStub($adapter);

        $this->assertTrue($repository->isHealthy());
    }

    public function testValidateAdapterAcceptsFakeAdapterName(): void
    {
        $adapter = new FakeMySQLAdapter($this->makeNonMySqlDriver());

        $repository = new MySQLRepositoryStub($adapter);

        $this->assertInstanceOf(MySQLRepositoryStub::class, $repository);
    }

    public function testValidateAdapterRejectsUnsupportedDriver(): void
    {
        $adapter = new RecordingMySQLAdapter($this->makeNonMySqlDriver());

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage(
            RepositoryException::driverNotSupported(RecordingMySQLAdapter::class)->getMessage()
        );

        new MySQLRepositoryStub($adapter, false);
    }

    /**
     * @return \MongoDB\Database|\Predis\Client|\Redis
     */
    private function makeNonMySqlDriver(): object
    {
        if (class_exists(\MongoDB\Database::class)) {
            /** @var \MongoDB\Database&MockObject $mongoDb */
            $mongoDb = $this->getMockBuilder(\MongoDB\Database::class)
                ->disableOriginalConstructor()
                ->getMock();

            return $mongoDb;
        }

        if (class_exists(\Predis\Client::class)) {
            /** @var \Predis\Client&MockObject $predis */
            $predis = $this->getMockBuilder(\Predis\Client::class)
                ->disableOriginalConstructor()
                ->getMock();

            return $predis;
        }

        if (class_exists(\Redis::class)) {
            /** @var \Redis&MockObject $redis */
            $redis = $this->getMockBuilder(\Redis::class)
                ->disableOriginalConstructor()
                ->getMock();

            return $redis;
        }

        $this->markTestSkipped('No MongoDB/Predis/Redis driver available to simulate unsupported MySQL driver.');
    }

}

class MySQLRepositoryStub extends BaseMySQLRepository
{
    protected string $tableName = 'items';

    public function __construct(AdapterInterface $adapter, bool $skipValidation = false)
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

    public function isHealthy(): bool
    {
        return $this->adapter->healthCheck();
    }
}

class DbalConnectionStub
{
}

class RecordingMySQLAdapter implements AdapterInterface
{
    public function __construct(
        private PDO|DoctrineDbalConnection|MongoDatabase|MongoClient|PredisClient|Redis $driver
    ) {
    }

    public function getDriver(): PDO|DoctrineDbalConnection|MongoDatabase|PredisClient|Redis
    {
        // AdapterInterface expects Database here, not MongoClient
        if ($this->driver instanceof MongoClient) {
            /** @var MongoDatabase */
            return $this->driver->selectDatabase('test');
        }

        return $this->driver;
    }

    public function getType(): string
    {
        return 'mysql';
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

    public function getConnection(): PDO|DoctrineDbalConnection|MongoClient|PredisClient|Redis
    {
        // If someone passed a MongoDatabase into this adapter, treat it as invalid for "connection"
        // (and in your tests, just don't pass MongoDatabase at all).
        if ($this->driver instanceof MongoDatabase) {
            throw new \LogicException('MongoDatabase is not a valid connection type for this test adapter.');
        }

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

class FakeMySQLAdapter extends RecordingMySQLAdapter
{
}

if (! class_exists(DoctrineDbalConnection::class)) {
    /** @codeCoverageIgnore */
    class_alias(DbalConnectionStub::class, DoctrineDbalConnection::class);
}
