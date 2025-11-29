<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 03:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Ops;

use Maatify\DataRepository\Generic\Support\RedisOps;
use PHPUnit\Framework\TestCase;

class RedisOpsTest extends TestCase
{
    public function testGetAndSetWithFakeDriver(): void
    {
        // Simulate a fake driver (array-based)
        $driver = new class () {
            /** @var array<string, mixed> */
            public array $store = [];

            /** @param string $key */
            public function get(string $key): mixed
            {
                return $this->store[$key] ?? null;
            }

            /** @param string $key */
            public function set(string $key, mixed $value): bool
            {
                $this->store[$key] = $value;
                return true;
            }

            /** @param string $key */
            public function del(string $key): int
            {
                if (isset($this->store[$key])) {
                    unset($this->store[$key]);
                    return 1;
                }
                return 0;
            }
            // No keys() method, forcing reflection usage
        };

        $ops = new RedisOps($driver);

        $this->assertTrue($ops->set('key1', 'value1'));
        $this->assertSame('value1', $ops->get('key1'));

        $this->assertSame(1, $ops->del('key1'));
        $this->assertNull($ops->get('key1'));
    }

    public function testKeysWithReflectionFallback(): void
    {
        $driver = new class () {
            // Must be public/protected/private "store" property
            /** @var array<string, mixed> */
            protected array $store = [
                'prefix:1' => 'val1',
                'prefix:2' => 'val2',
                'other:3' => 'val3'
            ];

            // Ops needs basic methods to not crash before keys check (though keys doesn't call them)
        };

        $ops = new RedisOps($driver);

        $keys = $ops->keys('prefix:*');

        $this->assertCount(2, $keys);
        $this->assertContains('prefix:1', $keys);
        $this->assertContains('prefix:2', $keys);
        $this->assertNotContains('other:3', $keys);
    }
}
