<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 18:30
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Pagination\Optimization;

use Maatify\DataRepository\Generic\GenericRedisRepository;
use PHPUnit\Framework\TestCase;

class RedisPaginationOptimizationTest extends TestCase
{
    public function testPaginateFetchesOnlyPagedKeys(): void
    {
        $spy = new SpyRedisDriver();

        // Seed 100 keys
        for ($i = 1; $i <= 100; $i++) {
            $spy->set("test:$i", json_encode(['id' => $i, 'name' => "Item $i"]));
        }

        $repo = new class($spy) extends GenericRedisRepository {
            protected string $keyPrefix = 'test:';

            public function __construct(private object $driver)
            {
                parent::__construct();
            }

            protected function getDriver(): object
            {
                return $this->driver;
            }
        };

        // Reset spy counters before pagination
        $spy->resetCounters();

        // Paginate: Page 2, 10 items per page
        $result = $repo->paginate(2, 10);

        // Verification
        $this->assertCount(10, $result->data);
        $this->assertEquals(100, $result->pagination->total);
        $this->assertEquals(2, $result->pagination->page);

        // Crucial Check:
        // keys() should be called once.
        // get() should be called EXACTLY 10 times (perPage), NOT 100 times.
        $this->assertEquals(1, $spy->callCounts['keys']);
        $this->assertEquals(10, $spy->callCounts['get'], "Redis 'get' should only be called for the items on the current page.");
    }
}

class SpyRedisDriver
{
    /** @var array<string, string> */
    public array $store = [];

    /** @var array<string, int> */
    public array $callCounts = [
        'get' => 0,
        'set' => 0,
        'keys' => 0,
    ];

    public function get(string $key): mixed
    {
        $this->callCounts['get']++;
        return $this->store[$key] ?? null;
    }

    public function set(string $key, string $value): bool
    {
        $this->callCounts['set']++;
        $this->store[$key] = $value;
        return true;
    }

    public function keys(string $pattern): array
    {
        $this->callCounts['keys']++;
        // Simple prefix match simulation
        $prefix = str_replace('*', '', $pattern);
        $matches = [];
        foreach (array_keys($this->store) as $k) {
            if (str_starts_with($k, $prefix)) {
                $matches[] = $k;
            }
        }
        return $matches;
    }

    public function resetCounters(): void
    {
        $this->callCounts = [
            'get' => 0,
            'set' => 0,
            'keys' => 0,
        ];
    }
}
