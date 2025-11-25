<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 01:39
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Base\Real;

use Maatify\DataAdapters\Adapters\MongoAdapter;
use Maatify\DataRepository\Base\BaseMongoRepository;
use Maatify\DataRepository\Tests\Helpers\RealAdapterTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
class RealMongoBaseRepositoryTest extends TestCase
{
    use RealAdapterTrait;

    public function testAcceptsMongoAdapter(): void
    {
        if (!class_exists(MongoAdapter::class) || !class_exists(\MongoDB\Client::class)) {
            $this->markTestSkipped('MongoDB extension or Adapter not installed');
        }

        $config = $this->getRealConfig();
        $adapter = new MongoAdapter($config, 'main');
        $adapter->connect();

        $driver = $adapter->getDriver();

        // Maatify Data Adapters (Phase 13+) returns MongoDB\Database
        // We cast to object to prevent "always true" static analysis warnings if the return type is strict
        $this->assertInstanceOf(\MongoDB\Database::class, (object)$driver);

        $repo = new class ($adapter) extends BaseMongoRepository {
            public function find(int|string $id): ?array
            {
                return null;
            }

            /**
             * @param array<string, mixed> $filters
             * @param array<string, string>|null $orderBy
             * @return array<int, array<string, mixed>>
             */
            public function findBy(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
            {
                return [];
            }

            /**
             * @param array<string, mixed> $filters
             * @return array<string, mixed>|null
             */
            public function findOneBy(array $filters): ?array
            {
                return empty($filters) ? null : ['id' => 1];
            }

            /** @return array<int, array<string, mixed>> */
            public function findAll(): array
            {
                return [];
            }

            /** @param array<string, mixed> $filters */
            public function count(array $filters = []): int
            {
                return 0;
            }

            /**
             * @param array<string, mixed> $data
             * @return int|string
             */
            public function insert(array $data): int|string
            {
                return empty($data) ? 1 : 'mongo_id_123';
            }

            /** @param array<string, mixed> $data */
            public function update(int|string $id, array $data): bool
            {
                return true;
            }

            public function delete(int|string $id): bool
            {
                return true;
            }
        };

        $this->assertInstanceOf(BaseMongoRepository::class, $repo);
    }
}
