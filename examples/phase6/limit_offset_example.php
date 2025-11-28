<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 06:10
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Generic\GenericMongoRepository;
use Maatify\DataRepository\Exceptions\RepositoryException;

// Note: This example assumes a bootstrap environment with connected adapters.

// 1. MySQL Example
class UserRepository extends GenericMySQLRepository {
    protected string $tableName = 'users';
}

try {
    /** @var UserRepository $mysqlRepo */
    $mysqlRepo = new UserRepository($mysqlAdapter); // $mysqlAdapter injected from app

    // Find top 10 users
    $users = $mysqlRepo->findBy(
        filters: ['status' => 'active'],
        orderBy: ['created_at' => 'DESC'],
        limit: 10,
        offset: 0
    );

    // Pagination (Page 2, 10 items per page)
    $page2 = $mysqlRepo->findBy(
        filters: [],
        orderBy: ['id' => 'ASC'],
        limit: 10,
        offset: 10
    );

    echo "Fetched " . count($users) . " users.\n";

} catch (RepositoryException $e) {
    echo "MySQL Error: " . $e->getMessage();
}

// 2. Mongo Example
class LogRepository extends GenericMongoRepository {
    protected string $collectionName = 'logs';
}

try {
    /** @var LogRepository $mongoRepo */
    $mongoRepo = new LogRepository($mongoAdapter);

    // Skip first 100 logs, take 50
    $logs = $mongoRepo->findBy(
        filters: ['level' => 'error'],
        orderBy: ['timestamp' => 'DESC'],
        limit: 50,
        offset: 100
    );

    echo "Fetched " . count($logs) . " logs.\n";

} catch (RepositoryException $e) {
    echo "Mongo Error: " . $e->getMessage();
}

// 3. Validation Example
try {
    // This will throw exception
    $mysqlRepo->findBy([], null, -5);
} catch (RepositoryException $e) {
    echo "Validation Caught: " . $e->getMessage() . "\n";
    // Output: Validation Caught: Invalid limit value: -5. Limit must be >= 1.
}
