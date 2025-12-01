<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-26
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\NoSQL;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericMongoRepository;
use PHPUnit\Framework\TestCase;

class MongoRobustnessTest extends TestCase
{
    private object $collectionMock;
    private AdapterInterface $adapterMock;

    protected function setUp(): void
    {
        $this->collectionMock = new class () {
            public string $name = '';
        };

        // Mock Adapter to return "fake" Mongo database object
        $this->adapterMock = $this->createMock(AdapterInterface::class);
        $this->adapterMock->method('getDriver')->willReturn(new class ($this->collectionMock) {
            public function __construct(private object $collection)
            {
            }
            public function selectCollection(string $db, string $collection): object
            {
                if (property_exists($this->collection, 'name')) {
                    $this->collection->name = $collection;
                }
                return $this->collection;
            }
        });
    }

    public function testCollectionNamingFallback(): void
    {
        // Mock Collection needed for instanceof check
        $collectionMock = $this->createMock(\MongoDB\Collection::class);

        // 1. Define repo with tableName only
        $repo = new class ($this->adapterMock, $collectionMock) extends GenericMongoRepository {
            protected string $tableName = 'users';
            public string $requestedName = '';

            public function __construct(AdapterInterface $adapter, private object $mockCollection)
            {
                parent::__construct($adapter);
            }

            protected function validateAdapter(): void
            {
                // Bypass validation for mock
            }

            protected function getCollection(string $collectionName): object
            {
                $this->requestedName = $collectionName;
                return $this->mockCollection;
            }
        };

        // Access via reflection to test private getCollectionObj logic
        $ref = new \ReflectionMethod(GenericMongoRepository::class, 'getCollectionObj');
        $ref->setAccessible(true);

        $col = $ref->invoke($repo);

        // Verify we got the collection object back
        $this->assertInstanceOf(\MongoDB\Collection::class, $col);
        // Verify the correct name was requested
        $this->assertEquals('users', $repo->requestedName);
    }

    public function testSetCollectionNameOverridesTable(): void
    {
        $collectionMock = $this->createMock(\MongoDB\Collection::class);

        $repo = new class ($this->adapterMock, $collectionMock) extends GenericMongoRepository {
            protected string $tableName = 'users';
            public string $requestedName = '';

            public function __construct(AdapterInterface $adapter, private object $mockCollection)
            {
                parent::__construct($adapter);
            }

            protected function validateAdapter(): void
            {
                // Bypass validation for mock
            }

            protected function getCollection(string $collectionName): object
            {
                $this->requestedName = $collectionName;
                return $this->mockCollection;
            }
        };

        $repo->setCollectionName('admins');

        $ref = new \ReflectionMethod(GenericMongoRepository::class, 'getCollectionObj');
        $ref->setAccessible(true);

        $col = $ref->invoke($repo);

        $this->assertEquals('admins', $repo->requestedName);
    }

    public function testMissingNameThrowsException(): void
    {
        $repo = new class ($this->adapterMock) extends GenericMongoRepository {
            protected string $tableName = ''; // Empty

            protected function validateAdapter(): void
            {
                // Bypass validation for mock
            }

            protected function getCollection(string $collectionName): object
            {
                return (object)[];
            }
        };

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Collection name not defined');

        $ref = new \ReflectionMethod(GenericMongoRepository::class, 'getCollectionObj');
        $ref->setAccessible(true);
        $ref->invoke($repo);
    }
}
