<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 16:59
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic;

use Maatify\DataFakes\Adapters\MySQL\FakeMySQLAdapter;
use Maatify\DataFakes\Storage\FakeStorageLayer;
use Maatify\DataRepository\Base\BaseMySQLRepository;
use PHPUnit\Framework\TestCase;

class CoverageProbeTest extends TestCase
{
    public function testProbe(): void
    {
        $storage = new FakeStorageLayer();
        $adapter = new FakeMySQLAdapter($storage);
        $repo = new class ($adapter) extends BaseMySQLRepository {
            protected string $tableName = 'test_table';
            public function find(int|string $id): ?array
            {
                return null;
            }

            /**
             * @param   array<string, mixed>        $filters
             * @param   array<string, string>|null  $orderBy
             *
             * @return array<int, array<string, mixed>>
             */
            public function findBy(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
            {
                return [];
            }

            /**
             * @param   array<string, mixed>  $filters
             *
             * @return array<string, mixed>|null
             */
            public function findOneBy(array $filters): ?array
            {
                // Conditional return to satisfy int|string coverage in analysis
                return empty($filters) ? null : ['id' => 1];
            }

            /** @return array<int, array<string, mixed>> */
            public function findAll(): array
            {
                return [];
            }

            /** @param   array<string, mixed>  $filters */
            public function count(array $filters = []): int
            {
                return 0;
            }

            /**
             * @param   array<string, mixed>  $data
             *
             * @return int|string
             */
            public function insert(array $data): int|string
            {
                // Conditional return to satisfy int|string coverage in analysis
                return empty($data) ? 1 : '1';
            }

            /** @param   array<string, mixed>  $data */
            public function update(int|string $id, array $data): bool
            {
                return true;
            }

            public function delete(int|string $id): bool
            {
                return true;
            }
        };

        // This line actually executes code inside src/BaseRepository
        // 🔥 This actually executes real code inside src/*
        $name = $repo->getTableName();

        // make sure the table name is correct
        $this->assertSame('test_table', $name);
    }
}
