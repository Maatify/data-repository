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
use Maatify\DataRepository\Generic\GenericMongoRepository;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Example: Mongo Robustness - Dynamic Collection Switching
 */

// 1. Setup Mock Adapter (Simulation)
$adapter = new class implements AdapterInterface {
    public function getDriver(): mixed { return new \stdClass(); } // Mock driver
    public function getType(): string { return 'mongo'; }
    public function connect(): void {}
    public function isConnected(): bool { return true; }
    public function disconnect(): void {}
    public function getConnection(): mixed { return null; }
    public function debugConfig(): object { return (object)[]; }
    public function healthCheck(): bool { return true; }
};

// 2. Define Repository with default table
class UserRepository extends GenericMongoRepository
{
    protected string $tableName = 'users';

    // Simulate collection retrieval for example purposes without real Mongo
    protected function getCollection(string $collectionName): object
    {
        echo "Requested Collection: " . $collectionName . PHP_EOL;
        return (object)['name' => $collectionName];
    }
}

$repo = new UserRepository($adapter);

// 3. Default behavior (uses tableName)
echo "--- Default Access ---" . PHP_EOL;
// Calling an internal method via public proxy for demo (or just trust the echo above if we could invoke it)
// In real usage, calling findAll() triggers getCollection('users')
try {
    $ref = new \ReflectionMethod($repo, 'getCollectionObj');
    $ref->setAccessible(true);
    $ref->invoke($repo);
} catch (\Exception $e) {
    // catch because our mock getCollection returns stdClass, not Collection
}

// 4. Switch Collection Dynamically
echo "--- Switching to 'archived_users' ---" . PHP_EOL;
$repo->setCollectionName('archived_users');

try {
    $ref->invoke($repo);
} catch (\Exception $e) {}

// 5. Reset to Default (empty string triggers fallback to tableName)
echo "--- Resetting to Default ---" . PHP_EOL;
$repo->setCollectionName('');

try {
    $ref->invoke($repo);
} catch (\Exception $e) {}
