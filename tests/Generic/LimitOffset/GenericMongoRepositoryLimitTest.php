<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 05:55
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\LimitOffset;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericMongoRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\Driver\CursorInterface;

class GenericMongoRepositoryLimitTest extends TestCase
{
    /** @var GenericMongoRepository<object>&MockObject */
    private GenericMongoRepository $repo;
    /** @var Collection&MockObject */
    private Collection $collection;

    protected function setUp(): void
    {
        if (! class_exists(Collection::class)) {
            $this->markTestSkipped('MongoDB library not installed');
        }

        $this->collection = $this->createMock(Collection::class);
        $db = $this->createMock(Database::class);
        $db->method('selectCollection')->willReturn($this->collection);
        $client = $this->createMock(Client::class);

        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->method('getDriver')->willReturn($db);

        /** @var GenericMongoRepository<object>&MockObject $repo */
        $repo = $this->getMockBuilder(GenericMongoRepository::class)
            ->setConstructorArgs([$adapter])
            // Do NOT mock getMongoOps because MongoOps is final.
            // We rely on the real getMongoOps which uses the real (but final) MongoOps class,
            // initialized with our mocked Collection.
            ->onlyMethods([])
            ->getMock();

        $this->repo = $repo;

        // Set collection name manually to avoid fallback logic noise
        $this->repo->setCollectionName('test');
    }

    public function testPaginateByValidatesLimitOffset(): void
    {
        // Phase 6 requires validation in paginateBy.
        $this->expectException(RepositoryException::class);

        // Use a limit exceeding the default max (e.g. 10001) to trigger exception
        $this->repo->paginateBy([], 1, 10001);
    }

    public function testFindByPassesLimitAndSkipToDriver(): void
    {
        // Use PHPUnit mock for CursorInterface instead of depending on Fake class
        $cursorMock = $this->createMock(CursorInterface::class);
        $cursorMock->method('toArray')->willReturn([]);

        $this->collection->expects($this->once())
            ->method('find')
            ->with([], ['limit' => 5, 'skip' => 10])
            ->willReturn($cursorMock);

        $this->repo->findBy([], null, 5, 10);
    }
}
