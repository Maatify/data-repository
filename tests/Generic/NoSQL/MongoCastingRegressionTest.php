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
use MongoDB\Driver\CursorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests for ADR-005 Compliance.
 * Ensures strict casting rules (no magic in findBy).
 */
class MongoCastingRegressionTest extends TestCase
{
    /** @var MockObject&\MongoDB\Collection */
    private $collectionMock;
    /** @var MockObject&AdapterInterface */
    private $adapterMock;
    /** @var GenericMongoRepository<object> */
    private GenericMongoRepository $repo;

    protected function setUp(): void
    {
        if (!class_exists(\MongoDB\Collection::class)) {
            $this->markTestSkipped('MongoDB library not installed');
        }

        // Mock Collection needed for instanceof check
        $this->collectionMock = $this->createMock(\MongoDB\Collection::class);

        // Mock Adapter
        $this->adapterMock = $this->createMock(AdapterInterface::class);
        $this->adapterMock->method('getDriver')->willReturn(new class ($this->collectionMock) {
            /**
             * @param \MongoDB\Collection&MockObject $collection
             */
            public function __construct(private object $collection) {}
            // Use variadic args to match any signature requirement for selectCollection
            public function selectCollection(mixed ...$args): object
            {
                return $this->collection;
            }
        });

        // Instantiate Repo
        /** @var GenericMongoRepository<object> $repo */
        $repo = new class ($this->adapterMock) extends GenericMongoRepository {
            protected string $collectionName = 'test_col';
            protected function validateAdapter(): void {} // bypass
        };
        $this->repo = $repo;
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
            ->willReturn(new FakeMongoCursor([]));

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
            ->willReturn(new FakeMongoCursor([]));

        $this->repo->findBy(['some_id' => $objectId]);
    }
}

/**
 * Fake Cursor satisfying MongoDB\Driver\CursorInterface.
 */
class FakeMongoCursor implements CursorInterface
{
    /**
     * @var array<int, array<string, mixed>|object|null>
     */
    private array $data;
    private int $position = 0;

    /**
     * @param   array<int, array<string, mixed>|object|null>  $data
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
        return $this->position < count($this->data);
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
        // Ensure return type is: array<int, array<string,mixed>|object>
        return array_values(
            array_filter(
                $this->data,
                static fn ($item) => $item !== null
            )
        );
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
