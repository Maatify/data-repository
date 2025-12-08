<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Exceptions;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericMongoRepository;
use MongoDB\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MongoExceptionTestEntity
{
    public string $id;
}

/**
 * @extends GenericMongoRepository<MongoExceptionTestEntity>
 */
class GenericMongoRepositoryExceptionStub extends GenericMongoRepository
{
    public function __construct(AdapterInterface $adapter)
    {
        parent::__construct($adapter);
        $this->collectionName = 'test_collection';
    }

    protected function createInstance(): object
    {
        return new MongoExceptionTestEntity();
    }
}

class GenericMongoRepositoryExceptionTest extends TestCase
{
    /** @var AdapterInterface&MockObject */
    private $adapter;

    /** @var Collection&MockObject */
    private $collection;

    private GenericMongoRepositoryExceptionStub $repository;

    protected function setUp(): void
    {
        // We cannot easily mock MongoDB\Collection if the extension is not loaded,
        // but composer.json suggests it might be available in dev.
        // If not, this test file might fail to load.
        // However, the prompt environment seems to lack composer but maybe has basic classes?
        // Assuming we can mock it or create a fake class if needed.
        // The codebase uses \MongoDB\Collection type hint, so it must exist.

        if (!class_exists(Collection::class)) {
            $this->markTestSkipped('MongoDB extension/library not available');
        }

        $this->collection = $this->createMock(Collection::class);

        // Create a fake driver that has selectCollection method
        // We use an anonymous class to satisfy the duck-typing in BaseMongoRepository::getCollection
        $driver = new class($this->collection) {
            private Collection $collection;
            public function __construct(Collection $collection) {
                $this->collection = $collection;
            }
            public function selectCollection(string $name): Collection {
                return $this->collection;
            }
        };

        // Mock adapter to return this fake driver
        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->adapter->method('getDriver')->willReturn($driver);

        $this->repository = new GenericMongoRepositoryExceptionStub($this->adapter);
    }

    public function testFindThrowsRepositoryException(): void
    {
        $this->collection->method('findOne')->willThrowException(new \Exception('Simulated Mongo Error'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Find failed: Simulated Mongo Error');

        $this->repository->find('507f1f77bcf86cd799439011');
    }

    public function testFindByThrowsRepositoryException(): void
    {
        $this->collection->method('find')->willThrowException(new \Exception('Simulated Mongo Error'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('FindBy failed: Simulated Mongo Error');

        $this->repository->findBy(['status' => 1]);
    }

    public function testFindOneByThrowsRepositoryException(): void
    {
        $this->collection->method('findOne')->willThrowException(new \Exception('Simulated Mongo Error'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('FindOneBy failed: Simulated Mongo Error');

        $this->repository->findOneBy(['status' => 1]);
    }

    public function testCountThrowsRepositoryException(): void
    {
        $this->collection->method('countDocuments')->willThrowException(new \Exception('Simulated Mongo Error'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Count failed: Simulated Mongo Error');

        $this->repository->count();
    }

    public function testInsertThrowsRepositoryException(): void
    {
        $this->collection->method('insertOne')->willThrowException(new \Exception('Simulated Mongo Error'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Insert failed: Simulated Mongo Error');

        $this->repository->insert(['col' => 'val']);
    }

    public function testUpdateThrowsRepositoryException(): void
    {
        $this->collection->method('updateOne')->willThrowException(new \Exception('Simulated Mongo Error'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Update failed: Simulated Mongo Error');

        $this->repository->update('507f1f77bcf86cd799439011', ['col' => 'val']);
    }

    public function testDeleteThrowsRepositoryException(): void
    {
        $this->collection->method('deleteOne')->willThrowException(new \Exception('Simulated Mongo Error'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Delete failed: Simulated Mongo Error');

        $this->repository->delete('507f1f77bcf86cd799439011');
    }
}
