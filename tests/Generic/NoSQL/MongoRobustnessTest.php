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
        $this->collectionMock = new class {
            public string $name = '';
        };

        // Mock Adapter to return "fake" Mongo database object
        $this->adapterMock = $this->createMock(AdapterInterface::class);
        $this->adapterMock->method('getDriver')->willReturn(new class($this->collectionMock) {
            public function __construct(private object $collection) {}
            public function selectCollection(string $db, string $collection): object {
                $this->collection->name = $collection;
                return $this->collection;
            }
        });
    }

    public function testCollectionNamingFallback(): void
    {
        // 1. Define repo with tableName only
        $repo = new class($this->adapterMock) extends GenericMongoRepository {
            protected string $tableName = 'users';

            // Override to return our mock directly instead of calling getDriver()->selectCollection
            // because BaseMongoRepository calls selectCollection via driver.
            // But wait, BaseMongoRepository implementation calls $this->getDriver()->selectCollection(...)
            // Let's rely on that.

            // We need to override getCollection because BaseMongoRepository expects
            // the driver to have selectCollection method.
            protected function getCollection(string $collectionName): object
            {
                // We return a dummy object that proves we asked for this name
                return (object)['name' => $collectionName, 'type' => 'MongoDB\Collection'];
            }
        };

        // Access via reflection to test private getCollectionObj logic
        $ref = new \ReflectionMethod(GenericMongoRepository::class, 'getCollectionObj');
        $ref->setAccessible(true);

        $col = $ref->invoke($repo);
        $this->assertEquals('users', $col->name);
    }

    public function testSetCollectionNameOverridesTable(): void
    {
        $repo = new class($this->adapterMock) extends GenericMongoRepository {
            protected string $tableName = 'users';

            protected function getCollection(string $collectionName): object
            {
                return (object)['name' => $collectionName, 'type' => 'MongoDB\Collection'];
            }
        };

        $repo->setCollectionName('admins');

        $ref = new \ReflectionMethod(GenericMongoRepository::class, 'getCollectionObj');
        $ref->setAccessible(true);

        $col = $ref->invoke($repo);
        $this->assertEquals('admins', $col->name);
    }

    public function testMissingNameThrowsException(): void
    {
        $repo = new class($this->adapterMock) extends GenericMongoRepository {
            protected string $tableName = ''; // Empty
            protected function getCollection(string $collectionName): object { return (object)[]; }
        };

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Collection name not defined');

        $ref = new \ReflectionMethod(GenericMongoRepository::class, 'getCollectionObj');
        $ref->setAccessible(true);
        $ref->invoke($repo);
    }
}
