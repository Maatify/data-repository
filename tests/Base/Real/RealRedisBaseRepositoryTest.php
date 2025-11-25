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

use Maatify\DataAdapters\Adapters\PredisAdapter;
use Maatify\DataAdapters\Adapters\RedisAdapter;
use Maatify\DataRepository\Base\BaseRedisRepository;
use Maatify\DataRepository\Tests\Helpers\RealAdapterTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
class RealRedisBaseRepositoryTest extends TestCase
{
    use RealAdapterTrait;

    public function testAcceptsPhpRedisAdapter(): void
    {
        if (!class_exists(RedisAdapter::class) || !extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension or Adapter not installed');
        }

        $config = $this->getRealConfig();
        $adapter = new RedisAdapter($config, 'main');
        $adapter->connect();

        $this->assertInstanceOf(\Redis::class, $adapter->getDriver());

        $repo = new class ($adapter) extends BaseRedisRepository {
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
                return empty($data) ? 1 : '1';
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

        $this->assertInstanceOf(BaseRedisRepository::class, $repo);
    }

    public function testAcceptsPredisAdapter(): void
    {
        if (!class_exists(PredisAdapter::class) || !class_exists(\Predis\Client::class)) {
            $this->markTestSkipped('Predis not installed');
        }

        $config = $this->getRealConfig();
        $adapter = new PredisAdapter($config, 'main');
        $adapter->connect();

        $this->assertInstanceOf(\Predis\Client::class, $adapter->getDriver());

        $repo = new class ($adapter) extends BaseRedisRepository {
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
                return empty($data) ? 1 : '1';
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

        $this->assertInstanceOf(BaseRedisRepository::class, $repo);
    }
}
