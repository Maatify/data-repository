<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-01
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Examples\Phase19;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use Maatify\DataRepository\Generic\Support\RedisOps;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Example: Redis In-Memory Filtering
 *
 * Note: This implementation fetches ALL keys matching the prefix and filters in PHP.
 * Suitable for small datasets (e.g., config, sessions, small lists).
 */

// 1. Setup Mock Adapter with In-Memory Store
$adapter = new class () implements AdapterInterface {
    public array $store = [
        'user:1' => '{"id":1, "name":"Alice", "role":"admin", "active":true}',
        'user:2' => '{"id":2, "name":"Bob", "role":"user", "active":true}',
        'user:3' => '{"id":3, "name":"Charlie", "role":"user", "active":false}',
    ];

    public function getDriver(): mixed
    {
        return $this;
    }
    public function getType(): string
    {
        return 'redis';
    }
    public function connect(): void
    {
    }
    public function isConnected(): bool
    {
        return true;
    }
    public function disconnect(): void
    {
    }
    public function getConnection(): mixed
    {
        return $this;
    }
    public function debugConfig(): object
    {
        return (object)[];
    }
    public function healthCheck(): bool
    {
        return true;
    }

    // RedisOps compatible methods
    public function get(string $key)
    {
        return $this->store[$key] ?? null;
    }
    public function keys(string $pattern)
    {
        return array_keys($this->store);
    }
};

// 2. Define Repository
class UserRedisRepository extends GenericRedisRepository
{
    protected string $keyPrefix = 'user:';
}

$repo = new UserRedisRepository($adapter);

// 3. Find By (Equality)
echo '--- Find Admins ---' . PHP_EOL;
$admins = $repo->findBy(['role' => 'admin']);
foreach ($admins as $u) {
    echo "Found: {$u['name']} ({$u['role']})" . PHP_EOL;
}

// 4. Find By (IN Array)
echo '--- Find Alice or Charlie ---' . PHP_EOL;
$subset = $repo->findBy(['name' => ['Alice', 'Charlie']]);
foreach ($subset as $u) {
    echo "Found: {$u['name']}" . PHP_EOL;
}

// 5. Pagination
echo '--- Pagination (Page 1, Limit 1) ---' . PHP_EOL;
$page = $repo->paginateBy(['active' => true], 1, 1);
echo 'Total Active: ' . $page->pagination->total . PHP_EOL;
echo 'Page Data: ' . $page->data[0]['name'] . PHP_EOL;
