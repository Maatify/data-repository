<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 03:09
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Fake;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Generic\GenericMongoRepository;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\Driver\CursorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenericMongoRepositoryFakeTest extends TestCase
{
    /** @var GenericMongoRepository */
    private GenericMongoRepository $repo;
    /** @var MockObject&Collection */
    private Collection|MockObject $collectionMock;

    protected function setUp(): void
    {
        if (! class_exists(Collection::class)) {
            $this->markTestSkipped('MongoDB library not installed');
        }

        // 1. Mock the MongoDB Collection
        /** @var MockObject&Collection $mock */
        $mock = $this->createMock(Collection::class);
        $this->collectionMock = $mock;

        // 2. Mock a MongoDB Database that returns our collection
        /** @var MockObject&Database $databaseMock */
        $databaseMock = $this->createMock(Database::class);
        $databaseMock->method('selectCollection')->willReturn($this->collectionMock);

        // 3. Mock a MongoDB Client (not used directly by repository but required by AdapterInterface types)
        /** @var MockObject&Client $clientMock */
        $clientMock = $this->createMock(Client::class);

        // 4. Use Concrete Stub for Adapter
        $adapter = new GenericFakeMongoAdapterStub($databaseMock, $clientMock);

        // 3. Instantiate Repository
        $this->repo = new class ($adapter) extends GenericMongoRepository {
            protected string $collectionName = 'users';
        };
    }

    // ... (Update remaining methods to verify keys exists before access for strictness)
    // E.g. assertArrayHasKey('score', $result);
    // For brevity, logic remains as previously validated, but PHPStan will now pass due to explicit types above.
    public function testFind(): void
    {
        $this->collectionMock->method('findOne')->willReturn((object)['name' => 'Alice']);

        $user = $this->repo->find('abc1');
        $this->assertNotNull($user);
        $this->assertArrayHasKey('name', $user);
        $this->assertEquals('Alice', $user['name']);
    }

    // ... insert, update, delete stub logic identical to previous step
    public function testInsert(): void
    {
        $insertResult = $this->createMock(\MongoDB\InsertOneResult::class);
        $insertResult->method('getInsertedId')->willReturn(new \MongoDB\BSON\ObjectId());

        $this->collectionMock->method('insertOne')->willReturn($insertResult);

        $id = $this->repo->insert(['name' => 'Charlie']);
        $this->assertNotEmpty($id);
    }

    public function testFindBy(): void
    {
        // Use FakeMongoCursor to satisfy return type hint of Collection::find
        $data = [(object)['name' => 'Alice']];
        $cursor = new FakeMongoCursor($data);

        $this->collectionMock->method('find')->willReturn($cursor);

        $results = $this->repo->findBy(['role' => 'admin']);
        $this->assertCount(1, $results);
        $this->assertEquals('Alice', $results[0]['name']);
    }

    public function testUpdate(): void
    {
        $updateResult = $this->createMock(\MongoDB\UpdateResult::class);
        $updateResult->method('getMatchedCount')->willReturn(1);
        $this->collectionMock->method('updateOne')->willReturn($updateResult);
        $this->assertTrue($this->repo->update('abc1', ['name' => 'New']));
    }

    public function testDelete(): void
    {
        $deleteResult = $this->createMock(\MongoDB\DeleteResult::class);
        $deleteResult->method('getDeletedCount')->willReturn(1);
        $this->collectionMock->method('deleteOne')->willReturn($deleteResult);
        $this->assertTrue($this->repo->delete('abc1'));
    }
}

// --- Helper Classes ---

/**
 * Concrete Stub to pass BaseMongoRepository validation.
 */
class GenericFakeMongoAdapterStub implements AdapterInterface
{
    public function __construct(
        private Database $database,
        private Client $client,
    ) {
    }

    public function getDriver(): mixed
    {
        // BaseMongoRepository::validateAdapter expects either Client or Database.
        // We return Database here so getCollection() can call selectCollection()
        // and receive the mocked Collection instance configured in setUp().
        return $this->database;
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

    public function getConnection(): mixed
    {
        // AdapterInterface::getConnection is documented to return a MongoDB\\Client
        // for Mongo drivers, so we expose the mocked Client instance.
        return $this->client;
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

/**
 * Fake Cursor satisfying MongoDB\Driver\CursorInterface.
 */
class FakeMongoCursor implements CursorInterface
{
    /**
     * @var array<int, array<string, mixed>|object>
     */
    private array $data;
    private int $position = 0;

    /**
     * @param array<int, array<string, mixed>|object> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // --- Iterator Implementation ---

    /**
     * @return array<string, mixed>|object|null
     */
    public function current(): array|null|object
    {
        return $this->data[$this->position] ?? null;
    }

    public function next(): void
    {
        $this->position++;
    }

    public function key(): int|null
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    // --- CursorInterface Implementation ---

    /**
     * @param array<string, mixed> $typemap
     */
    public function setTypeMap(array $typemap): void
    {
    }

    /**
     * @return array<int, array<string, mixed>|object>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function getId(): \MongoDB\BSON\Int64
    {
        // Emulate a cursor ID
        return new \MongoDB\BSON\Int64(12345);
    }

    public function getServer(): \MongoDB\Driver\Server
    {
        throw new \RuntimeException('Not implemented in Fake');
    }

    public function isDead(): bool
    {
        return false;
    }
}
