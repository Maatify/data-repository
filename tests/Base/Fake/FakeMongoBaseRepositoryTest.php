<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 01:09
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Base\Fake;

use Maatify\DataFakes\Adapters\MySQL\FakeMySQLAdapter;
use Maatify\DataFakes\Storage\FakeStorageLayer;
use Maatify\DataRepository\Base\BaseMongoRepository;
use Maatify\DataRepository\Exceptions\RepositoryException;
use PHPUnit\Framework\TestCase;

class FakeMongoBaseRepositoryTest extends TestCase
{
    public function testAcceptsFakeMongoAdapter(): void
    {
        $invalidAdapter = new class () extends FakeMySQLAdapter {
            public function __construct()
            {
                parent::__construct(new FakeStorageLayer());
            }
        };

        $this->expectException(RepositoryException::class);

        new class ($invalidAdapter) extends BaseMongoRepository {
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
                return empty($filters) ? null : ['id' => 'abc'];
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
    }
}
