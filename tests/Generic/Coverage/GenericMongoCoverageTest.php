<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 03:03
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Coverage;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\DriverOperationException;
use Maatify\DataRepository\Exceptions\QueryExecutionException;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericMongoRepository;
use MongoDB\Collection;
use MongoDB\InsertOneResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenericMongoCoverageTest extends TestCase
{
    /** @var AdapterInterface&MockObject */
    private AdapterInterface $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);
    }

    public function testGetCollectionObjThrowsExceptionIfNameMissing(): void
    {
        // Use anonymous class to extend GenericMongoRepository
        // We bypass the constructor to avoid validation logic triggering prematurely
        $repo = new class ($this->adapter) extends GenericMongoRepository {
            public function __construct(AdapterInterface $adapter)
            {
                // Bypass parent constructor
                $this->adapter = $adapter;
                // Leave tableName empty
                $this->tableName = '';
            }

            public function testFind(): void
            {
                $this->find('123');
            }
        };

        $this->expectException(QueryExecutionException::class);
        $this->expectExceptionMessage('Find operation failed.');

        $repo->testFind();
    }

    public function testGetCollectionObjThrowsExceptionIfDriverFails(): void
    {
        // Mock getCollection to return null
        $repo = new class ($this->adapter) extends GenericMongoRepository {
            public function __construct(AdapterInterface $adapter)
            {
                $this->adapter = $adapter;
                $this->tableName = 'test';
            }

            protected function getCollection(string $name): mixed
            {
                return null;
            }

            public function testFind(): void
            {
                $this->find('123');
            }
        };

        $this->expectException(QueryExecutionException::class);
        $this->expectExceptionMessage('Find operation failed.');

        $repo->testFind();
    }

    public function testInsertThrowsExceptionOnInvalidId(): void
    {
        $collection = $this->createMock(Collection::class);
        $result = $this->createMock(InsertOneResult::class);

        $result->method('getInsertedId')->willReturn(new \stdClass()); // Invalid ID type
        $collection->method('insertOne')->willReturn($result);

        $repo = new class ($this->adapter, $collection) extends GenericMongoRepository {
            private object $mockCollection;

            public function __construct(AdapterInterface $adapter, object $mockCollection)
            {
                $this->adapter = $adapter;
                $this->tableName = 'test';
                $this->mockCollection = $mockCollection;
            }

            protected function getCollection(string $name): mixed
            {
                return $this->mockCollection;
            }
        };

        $this->expectException(DriverOperationException::class);
        $this->expectExceptionMessage('Insert failed');

        $repo->insert(['a' => 1]);
    }

    public function testExceptionHandlingInCrud(): void
    {
        $collection = $this->createMock(Collection::class);
        $exception = new \Exception('Simulated Mongo Error');

        $collection->method('findOne')->willThrowException($exception);
        $collection->method('find')->willThrowException($exception);
        $collection->method('countDocuments')->willThrowException($exception);
        $collection->method('insertOne')->willThrowException($exception);
        $collection->method('updateOne')->willThrowException($exception);
        $collection->method('deleteOne')->willThrowException($exception);

        $repo = new class ($this->adapter, $collection) extends GenericMongoRepository {
            private object $mockCollection;

            public function __construct(AdapterInterface $adapter, object $mockCollection)
            {
                $this->adapter = $adapter;
                $this->tableName = 'test';
                $this->mockCollection = $mockCollection;
            }

            protected function getCollection(string $name): mixed
            {
                return $this->mockCollection;
            }
        };

        // Find
        try {
            $repo->find('123');
            $this->fail('Expected exception');
        } catch (QueryExecutionException $e) {
            $this->assertSame('Find operation failed.', $e->getMessage());
        }

        // FindBy
        try {
            $repo->findBy([]);
            $this->fail('Expected exception');
        } catch (QueryExecutionException $e) {
            $this->assertSame('FindBy operation failed.', $e->getMessage());
        }

        // FindOneBy
        try {
            $repo->findOneBy([]);
            $this->fail('Expected exception');
        } catch (QueryExecutionException $e) {
            $this->assertSame('FindOneBy operation failed.', $e->getMessage());
        }

        // Count
        try {
            $repo->count();
            $this->fail('Expected exception');
        } catch (QueryExecutionException $e) {
            $this->assertSame('Count operation failed.', $e->getMessage());
        }

        // Insert
        try {
            $repo->insert(['a' => 1]);
            $this->fail('Expected exception');
        } catch (DriverOperationException $e) {
            $this->assertSame('Insert operation failed.', $e->getMessage());
        }

        // Update
        try {
            $repo->update('123', ['a' => 1]);
            $this->fail('Expected exception');
        } catch (DriverOperationException $e) {
            $this->assertSame('Update operation failed.', $e->getMessage());
        }

        // Delete
        try {
            $repo->delete('123');
            $this->fail('Expected exception');
        } catch (DriverOperationException $e) {
            $this->assertSame('Delete operation failed.', $e->getMessage());
        }
    }
}
