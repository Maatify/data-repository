<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-26
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\NoSQL;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use PHPUnit\Framework\TestCase;

class RedisFilteringTest extends TestCase
{
    private GenericRedisRepository $repo;
    private object $fakeRedis;

    protected function setUp(): void
    {
        // 1. Create a FakeRedis in-memory store
        $this->fakeRedis = new class {
            public array $store = [];

            public function get(string $key) {
                return $this->store[$key] ?? null;
            }

            public function set(string $key, string $value): bool {
                $this->store[$key] = $value;
                return true;
            }

            public function del(string $key): int {
                if (isset($this->store[$key])) {
                    unset($this->store[$key]);
                    return 1;
                }
                return 0;
            }

            // GenericRedisRepository uses RedisOps which might use reflection to find 'store'
            // or we can implement keys()
            public function keys(string $pattern): array {
                // simple prefix match logic
                $prefix = str_replace('*', '', $pattern);
                $result = [];
                foreach (array_keys($this->store) as $k) {
                    if (str_starts_with((string)$k, $prefix)) {
                        $result[] = $k;
                    }
                }
                return $result;
            }
        };

        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->method('getDriver')->willReturn($this->fakeRedis);

        $this->repo = new class($adapter) extends GenericRedisRepository {
            protected string $keyPrefix = 'test:';
        };

        // Seed Data
        $this->repo->insert(['id' => 1, 'name' => 'Alice', 'role' => 'admin', 'age' => 30]);
        $this->repo->insert(['id' => 2, 'name' => 'Bob',   'role' => 'user',  'age' => 25]);
        $this->repo->insert(['id' => 3, 'name' => 'Charlie', 'role' => 'user', 'age' => 35]);
    }

    public function testFindByEquality(): void
    {
        $users = $this->repo->findBy(['role' => 'user']);
        $this->assertCount(2, $users);

        $names = array_column($users, 'name');
        sort($names);
        $this->assertEquals(['Bob', 'Charlie'], $names);
    }

    public function testFindOneBy(): void
    {
        $admin = $this->repo->findOneBy(['role' => 'admin']);
        $this->assertNotNull($admin);
        $this->assertEquals('Alice', $admin['name']);

        $missing = $this->repo->findOneBy(['role' => 'superadmin']);
        $this->assertNull($missing);
    }

    public function testPaginateBy(): void
    {
        // Filter users (2 total), page 1, limit 1
        $result = $this->repo->paginateBy(['role' => 'user'], 1, 1);

        $this->assertCount(1, $result->data);
        $this->assertEquals(2, $result->pagination->total);
        $this->assertEquals(2, $result->pagination->totalPages);
        $this->assertEquals('Bob', $result->data[0]['name']); // Default order (insertion/keys usually)
    }

    public function testFindBySorting(): void
    {
        // Sort by age DESC
        $users = $this->repo->findBy(['role' => 'user'], ['age' => 'DESC']);

        $this->assertCount(2, $users);
        $this->assertEquals('Charlie', $users[0]['name']); // 35
        $this->assertEquals('Bob', $users[1]['name']);     // 25
    }

    public function testFindByWithLimitAndOffset(): void
    {
        // user role sorted by age ASC: Bob(25), Charlie(35)
        // Offset 1, Limit 1 -> should return Charlie
        $users = $this->repo->findBy(['role' => 'user'], ['age' => 'ASC'], 1, 1);

        $this->assertCount(1, $users);
        $this->assertEquals('Charlie', $users[0]['name']);
    }
}
