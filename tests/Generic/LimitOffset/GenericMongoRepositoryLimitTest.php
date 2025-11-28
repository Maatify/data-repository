<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 05:45
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\LimitOffset;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericMongoRepository;
use PHPUnit\Framework\TestCase;

class GenericMongoRepositoryLimitTest extends TestCase
{
    private GenericMongoRepository $repo;

    protected function setUp(): void
    {
        // Mock Mongo Collection and Cursor
        // Since we can't easily use real Mongo in memory, we mock the Collection and find()
        // But the purpose here is to test the VALIDATION logic inside the repo,
        // so we don't even need the find() to return anything if we expect an exception first.

        $adapter = $this->createMock(AdapterInterface::class);
        // We don't need driver for validation check if validation happens before retrieval.
        // But for success case we need to mock driver.

        $collection = $this->createMock(\MongoDB\Collection::class);
        $adapter->method('getDriver')->willReturn($this->createMock(\MongoDB\Client::class));

        // However, GenericMongoRepository uses getCollection($name) from BaseMongoRepository which calls adapter->getDriver()->selectCollection...
        // Let's create an anonymous class that mocks getCollectionObj or overrides it.

        $this->repo = new class ($adapter) extends GenericMongoRepository {
            protected string $collectionName = 'users';
            public ?\MongoDB\Collection $mockCollection = null;

            // Override to return mock directly for testing
            protected function getCollection(string $name): mixed
            {
                return $this->mockCollection;
            }
        };

        // We can inject a mock collection if needed
        $this->repo->mockCollection = $collection;
    }

    public function testInvalidLimit(): void
    {
        $this->expectException(RepositoryException::class);
        $this->repo->findBy([], null, -1);
    }

    public function testInvalidOffset(): void
    {
        $this->expectException(RepositoryException::class);
        $this->repo->findBy([], null, null, -5);
    }
}
