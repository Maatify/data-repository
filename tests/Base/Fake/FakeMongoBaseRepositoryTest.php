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
    public function testRejectsInvalidFakeMySQLAdapter(): void
    {
        $invalidAdapter = new FakeMySQLAdapter(new FakeStorageLayer());

        $this->expectException(RepositoryException::class);

        new class ($invalidAdapter) extends BaseMongoRepository {
            protected string $tableName = 'logs';
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

    public function testAcceptsFakeMongoAdapter(): void
    {
        $invalidAdapter = new class () extends FakeMySQLAdapter {
            public function __construct()
            {
                parent::__construct(new FakeStorageLayer());
            }
        };

        $this->expectException(RepositoryException::class);

        $repo = new class ($invalidAdapter) extends BaseMongoRepository {
            protected string $tableName = 'logs';
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

        $this->assertInstanceOf(BaseMongoRepository::class, $repo);
        $this->assertSame('logs', $repo->getTableName());

        // Method coverage (hit both branches)

        // find()
        $this->assertNull($repo->find(999));
        $this->assertSame(['ok' => true], $repo->find(1));

        // findBy()
        $this->assertSame([], $repo->findBy([]));
        $this->assertSame([['id' => 1]], $repo->findBy(['a' => 1]));

        // findOneBy()
        $this->assertNull($repo->findOneBy([]));
        $this->assertSame(['x' => 1], $repo->findOneBy(['a' => 1]));

        // findAll()
        $this->assertSame([['one' => 1]], $repo->findAll());

        // count()
        $this->assertSame(0, $repo->count([]));
        $this->assertSame(5, $repo->count(['a' => 1]));

        // insert()
        $this->assertSame(1, $repo->insert([]));
        $this->assertSame('mongo123', $repo->insert(['a' => 1]));

        // update()
        $this->assertFalse($repo->update(1, []));
        $this->assertTrue($repo->update(1, ['x' => 1]));

        // delete()
        $this->assertTrue($repo->delete(1));
        $this->assertFalse($repo->delete(999));
    }
}
