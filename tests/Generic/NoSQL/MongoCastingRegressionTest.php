<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-20
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\NoSQL;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Generic\GenericMongoRepository;
use MongoDB\BSON\ObjectId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests for ADR-005 Compliance.
 * Ensures strict casting rules (no magic in findBy).
 */
class MongoCastingRegressionTest extends TestCase
{
    private object $collectionMock;
    private AdapterInterface $adapterMock;
    private GenericMongoRepository $repo;

    protected function setUp(): void
    {
        if (!class_exists(\MongoDB\Collection::class)) {
            $this->markTestSkipped('MongoDB library not installed');
        }

        // Mock Collection needed for instanceof check
        /** @var MockObject&\MongoDB\Collection $collectionMock */
        $collectionMock = $this->createMock(\MongoDB\Collection::class);
        $this->collectionMock = $collectionMock;

        // Mock Adapter
        $this->adapterMock = $this->createMock(AdapterInterface::class);
        $this->adapterMock->method('getDriver')->willReturn(new class ($this->collectionMock) {
            public function __construct(private object $collection) {}
            public function selectCollection(string $db, string $collection): object
            {
                return $this->collection;
            }
        });

        // Instantiate Repo
        $this->repo = new class ($this->adapterMock) extends GenericMongoRepository {
            protected string $collectionName = 'test_col';
            protected function validateAdapter(): void {} // bypass
        };
    }

    /**
     * Regression Test A:
     * A 24-char hex string passed to findBy() MUST NOT be cast to ObjectId.
     */
    public function testFindByTreatsHexStringAsLiteral(): void
    {
        $hexString = '507f1f77bcf86cd799439011';

        // Expect find() on collection to receive the string EXACTLY as provided
        $this->collectionMock
            ->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(function (array $filter) use ($hexString) {
                    // Ensure the key exists
                    if (!isset($filter['some_id'])) {
                        return false;
                    }

                    $value = $filter['some_id'];

                    // FAIL if it was cast to ObjectId
                    if ($value instanceof ObjectId) {
                        return false;
                    }

                    // PASS if it is exactly the string
                    return $value === $hexString;
                }),
                $this->anything() // options
            )
            ->willReturn(new \ArrayIterator([])); // Return empty cursor

        $this->repo->findBy(['some_id' => $hexString]);
    }

    /**
     * Regression Test B:
     * Explicit new ObjectId(...) passed to filters MUST be preserved.
     */
    public function testFindByPreservesExplicitObjectId(): void
    {
        $objectId = new ObjectId('507f1f77bcf86cd799439011');

        $this->collectionMock
            ->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(function (array $filter) use ($objectId) {
                    if (!isset($filter['some_id'])) {
                        return false;
                    }
                    $value = $filter['some_id'];

                    // PASS if it is the ObjectId instance
                    return $value instanceof ObjectId && (string)$value === (string)$objectId;
                }),
                $this->anything()
            )
            ->willReturn(new \ArrayIterator([]));

        $this->repo->findBy(['some_id' => $objectId]);
    }
}
