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
use RuntimeException;

class IntegrationMatrixExample
{
    // This example demonstrates how the Integration Matrix works conceptually.
    // In a real usage scenario, this would be part of the test suite (as seen in FakeVsRealMatrixTest.php).

    public function runMatrix(): void
    {
        $mysqlRepo = $this->createFakeMySQLRepo();
        $this->validateRepository($mysqlRepo, 'MySQL');

        $mongoRepo = $this->createFakeMongoRepo();
        $this->validateRepository($mongoRepo, 'Mongo');

        echo "Integration Matrix validation passed for all drivers.\n";
    }

    private function validateRepository(object $repo, string $driver): void
    {
        // 1. Insert
        $data = ['name' => "Test Item $driver", 'val' => 100];
        /** @var int $id */
        $id = $repo->insert($data);

        // 2. Find
        /** @var array $found */
        $found = $repo->find($id);
        if ($found['name'] !== "Test Item $driver") {
            throw new RuntimeException("$driver: Name mismatch");
        }

        // 3. Update
        $repo->update($id, ['val' => 200]);
        /** @var array $updated */
        $updated = $repo->find($id);
        if ($updated['val'] !== 200) {
            throw new RuntimeException("$driver: Update failed");
        }

        // 4. Delete
        $repo->delete($id);
        if ($repo->find($id) !== null) {
            throw new RuntimeException("$driver: Delete failed");
        }
    }

    private function createFakeMySQLRepo(): object
    {
        // ... (Implementation similar to FakeVsRealMatrixTest) ...
        // Simplification for example purposes:
        return new class(new class implements AdapterInterface {
            public function connect(): void {}
            public function disconnect(): void {}
            public function getConnection(): mixed { return null; } // @phpstan-ignore-line
            public function getDriver(): mixed { return null; } // @phpstan-ignore-line
            public function healthCheck(): bool { return true; }
            public function isConnected(): bool { return true; }
            public function go(): void {}
        }, null) extends GenericMySQLRepository {
            private array $store = [];
            private int $idx = 0;
            protected function getPdo(): PDO { throw new RuntimeException("Fake"); }
            public function insert(array $data): int { $this->idx++; $data['id'] = $this->idx; $this->store[$this->idx] = $data; return $this->idx; }
            public function find(int|string $id): ?array { return $this->store[$id] ?? null; }
            public function update(int|string $id, array $data): bool {
                if(!isset($this->store[$id])) return false;
                $this->store[$id] = array_merge($this->store[$id], $data);
                return true;
            }
            public function delete(int|string $id): bool { unset($this->store[$id]); return true; }
            public function validateAdapter(): void {}
            protected function getDriver(): mixed { return null; }
        };
    }

    private function createFakeMongoRepo(): object
    {
        return new class(new class implements AdapterInterface {
            public function connect(): void {}
            public function disconnect(): void {}
            public function getConnection(): mixed { return null; } // @phpstan-ignore-line
            public function getDriver(): mixed { return null; } // @phpstan-ignore-line
            public function healthCheck(): bool { return true; }
            public function isConnected(): bool { return true; }
            public function go(): void {}
        }, null) extends GenericMongoRepository {
            private array $store = [];
            private int $idx = 0;
            public function insert(array $data): int { $this->idx++; $data['id'] = $this->idx; $this->store[$this->idx] = $data; return $this->idx; }
            public function find(int|string $id): ?array { return $this->store[$id] ?? null; }
            public function update(int|string $id, array $data): bool {
                if(!isset($this->store[$id])) return false;
                $this->store[$id] = array_merge($this->store[$id], $data);
                return true;
            }
            public function delete(int|string $id): bool { unset($this->store[$id]); return true; }
            public function validateAdapter(): void {}
            protected function getCollectionObj(): \MongoDB\Collection { throw new \Exception("Mock"); }
            protected function getDriver(): mixed { return null; }
        };
    }
}
