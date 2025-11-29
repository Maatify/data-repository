<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 10:00
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Generic\GenericMongoRepository;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use Maatify\DataRepository\Generic\Support\MysqlOps;
use Maatify\DataRepository\Generic\Support\MongoOps;
use Maatify\DataRepository\Generic\Support\RedisOps;

// This example demonstrates how the internal Ops classes are exposed
// (via protected methods) to help implement custom repository logic if needed.

// -----------------------------------------------------------------------------
// 1. MySQL Example
// -----------------------------------------------------------------------------
class ExtendedUserRepository extends GenericMySQLRepository
{
    protected string $tableName = 'users';

    // Expose Ops for demonstration (normally protected)
    public function getOps(): MysqlOps
    {
        return $this->getMysqlOps();
    }

    // Demonstrate usage of Ops in a custom method
    public function directInsert(array $data): int|string
    {
        // Use raw PDO driver via Ops
        $pdo = $this->getOps()->getDriver();

        if ($pdo instanceof PDO) {
            $stmt = $pdo->prepare('INSERT INTO users (name) VALUES (:name)');
            $stmt->execute(['name' => $data['name']]);

            // Use Ops to normalize ID
            return $this->getOps()->lastInsertId();
        }

        return 0;
    }
}

if (! isset($mysqlAdapter)) {
    echo "[MySQL] Skipping: \$mysqlAdapter not set.\n";
} else {
    try {
        echo "[MySQL] Running Ops Demo...\n";
        /** @var ExtendedUserRepository $mysqlRepo */
        $mysqlRepo = new ExtendedUserRepository($mysqlAdapter);

        $id = $mysqlRepo->directInsert(['name' => 'Ops User']);
        echo ' - Inserted User ID via Ops: ' . $id . "\n";

    } catch (Exception $e) {
        echo ' - [MySQL] Error: ' . $e->getMessage() . "\n";
    }
}

echo "\n";

// -----------------------------------------------------------------------------
// 2. MongoDB Example
// -----------------------------------------------------------------------------
class ExtendedLogRepository extends GenericMongoRepository
{
    protected string $collectionName = 'app_logs';

    public function getOps(): MongoOps
    {
        return $this->getMongoOps();
    }

    public function rawInsert(array $data): string
    {
        // Access raw collection
        $collection = $this->getOps()->getCollection();

        if (method_exists($collection, 'insertOne')) {
            $result = $collection->insertOne($data);

            // Use Ops to normalize the BSON ObjectId to string
            return (string) $this->getOps()->normalizeInsertedId($result->getInsertedId());
        }

        return '';
    }
}

if (! isset($mongoAdapter)) {
    echo "[Mongo] Skipping: \$mongoAdapter not set.\n";
} else {
    try {
        echo "[Mongo] Running Ops Demo...\n";
        /** @var ExtendedLogRepository $mongoRepo */
        $mongoRepo = new ExtendedLogRepository($mongoAdapter);

        $id = $mongoRepo->rawInsert(['msg' => 'Test Log', 'ts' => time()]);
        echo ' - Inserted Log ID via Ops: ' . $id . "\n";

    } catch (Exception $e) {
        echo ' - [Mongo] Error: ' . $e->getMessage() . "\n";
    }
}

echo "\n";

// -----------------------------------------------------------------------------
// 3. Redis Example
// -----------------------------------------------------------------------------
class ExtendedCacheRepository extends GenericRedisRepository
{
    protected string $keyPrefix = 'cache:';

    public function getOps(): RedisOps
    {
        return $this->getRedisOps();
    }

    public function scanKeys(): array
    {
        // Use Ops to scan keys (works on Real Redis, Predis, and Fakes)
        return $this->getOps()->keys($this->keyPrefix . '*');
    }
}

if (! isset($redisAdapter)) {
    echo "[Redis] Skipping: \$redisAdapter not set.\n";
} else {
    try {
        echo "[Redis] Running Ops Demo...\n";
        /** @var ExtendedCacheRepository $redisRepo */
        $redisRepo = new ExtendedCacheRepository($redisAdapter);

        // Seed
        $redisRepo->insert(['id' => 'config_1', 'val' => 'on']);

        // Use exposed Ops method
        $keys = $redisRepo->scanKeys();
        echo ' - Found ' . count($keys) . " keys via Ops.\n";

    } catch (Exception $e) {
        echo ' - [Redis] Error: ' . $e->getMessage() . "\n";
    }
}
