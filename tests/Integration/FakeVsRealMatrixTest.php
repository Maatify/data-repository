<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 20:05
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Integration;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Generic\GenericMongoRepository;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;

class FakeVsRealMatrixTest extends IntegrationValidatorTest
{
    /**
     * @param string $adapterType
     * @param GenericMySQLRepository|GenericMongoRepository $repository
     */
    #[DataProvider('adapterProvider')]
    public function testCrudConsistency(string $adapterType, object $repository): void
    {
        // 1. Insert
        $data = ['name' => 'Matrix Test', 'value' => 123];
        $id = $repository->insert($data);
        $this->assertNotEmpty($id, "Insert should return an ID for $adapterType");

        // 2. Find
        $found = $repository->find($id);
        $this->assertNotNull($found, "Should find record by ID in $adapterType");
        // Removed assertIsArray as find() return type ?array guarantees array if not null
        $this->assertEquals('Matrix Test', $found['name']);

        // 3. Update
        $updated = $repository->update($id, ['value' => 456]);
        $this->assertTrue($updated, "Update should return true for $adapterType");

        $foundAfterUpdate = $repository->find($id);
        $this->assertNotNull($foundAfterUpdate);
        $this->assertEquals(456, $foundAfterUpdate['value']);

        // 4. Delete
        $deleted = $repository->delete($id);
        $this->assertTrue($deleted, "Delete should return true for $adapterType");

        $foundAfterDelete = $repository->find($id);
        $this->assertNull($foundAfterDelete, "Record should be gone after delete in $adapterType");
    }

    /**
     * @return array<string, array{0: string, 1: object}>
     */
    public static function adapterProvider(): array
    {
        return [
            'MySQL Fake' => ['MySQL', self::createFakeMySQLRepo()],
            'Mongo Fake' => ['Mongo', self::createFakeMongoRepo()],
        ];
    }

    private static function createFakeMySQLRepo(): object
    {
        $dummyAdapter = new class implements AdapterInterface {
            public function connect(): void
            {
            }
            public function disconnect(): void
            {
            }
            /** @phpstan-ignore-next-line */
            public function getConnection(): mixed
            {
                return null;
            }
            /** @phpstan-ignore-next-line */
            public function getDriver(): mixed
            {
                return null;
            }
            public function healthCheck(): bool
            {
                return true;
            }
            public function isConnected(): bool
            {
                return true;
            }
            public function go(): void
            {
            } // Extra method to satisfy prev code if any
        };

        return new class ($dummyAdapter, null) extends GenericMySQLRepository {
            /** @var array<int|string, array<string, mixed>> */
            private array $storage = [];
            private int $lastId = 0;

            protected function getPdo(): PDO
            {
                throw new \RuntimeException("Fake overrides CRUD, getPdo should not be called.");
            }

            // Narrowed return type to int to satisfy PHPStan unusedType check
            public function insert(array $data): int
            {
                $this->lastId++;
                $data['id'] = $this->lastId;
                $this->storage[$this->lastId] = $data;
                return $this->lastId;
            }

            public function find(int|string $id): ?array
            {
                return $this->storage[$id] ?? null;
            }

            public function update(int|string $id, array $data): bool
            {
                if (! isset($this->storage[$id])) {
                    return false;
                }
                /** @var array<string, mixed> $existing */
                $existing = $this->storage[$id];
                $this->storage[$id] = array_merge($existing, $data);
                return true;
            }

            public function delete(int|string $id): bool
            {
                if (! isset($this->storage[$id])) {
                    return false;
                }
                unset($this->storage[$id]);
                return true;
            }

            public function validateAdapter(): void
            {
            }
            protected function getDriver(): mixed
            {
                return null;
            }
        };
    }

    private static function createFakeMongoRepo(): object
    {
        $dummyAdapter = new class implements AdapterInterface {
            public function connect(): void
            {
            }
            public function disconnect(): void
            {
            }
            /** @phpstan-ignore-next-line */
            public function getConnection(): mixed
            {
                return null;
            }
            /** @phpstan-ignore-next-line */
            public function getDriver(): mixed
            {
                return null;
            }
            public function healthCheck(): bool
            {
                return true;
            }
            public function isConnected(): bool
            {
                return true;
            }
            public function go(): void
            {
            }
        };

        return new class ($dummyAdapter, null) extends GenericMongoRepository {
            /** @var array<int|string, array<string, mixed>> */
            private array $storage = [];
            private int $lastId = 0;

            // Narrowed return type to int
            public function insert(array $data): int
            {
                $this->lastId++;
                $data['id'] = $this->lastId;
                $this->storage[$this->lastId] = $data;
                return $this->lastId;
            }

            public function find(int|string $id): ?array
            {
                return $this->storage[$id] ?? null;
            }

            public function update(int|string $id, array $data): bool
            {
                if (! isset($this->storage[$id])) {
                    return false;
                }
                /** @var array<string, mixed> $existing */
                $existing = $this->storage[$id];
                $this->storage[$id] = array_merge($existing, $data);
                return true;
            }

            public function delete(int|string $id): bool
            {
                if (! isset($this->storage[$id])) {
                    return false;
                }
                unset($this->storage[$id]);
                return true;
            }

            public function validateAdapter(): void
            {
            }
            protected function getCollectionObj(): \MongoDB\Collection
            {
                throw new \Exception("Mock");
            }
            protected function getDriver(): mixed
            {
                return null;
            }
        };
    }
}
