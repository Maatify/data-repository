<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\Support\OrderUtils;
use Maatify\DataRepository\Generic\Support\MySQLOrderBuilder;
use Maatify\DataRepository\Generic\Support\MongoOrderBuilder;

// --- 1. Using OrderUtils (Backward Compatibility) ---

echo "=== 1. Using OrderUtils (Facade) ===\n";

// Normalized Array
$orderBy = ['name' => 'ASC', 'age' => 'desc', 'created_at' => 'Invalid'];
$normalized = OrderUtils::normalize($orderBy);
echo 'Normalized Order: ' . json_encode($normalized) . "\n";
// Output: {"name":"ASC","age":"DESC","created_at":"ASC"}

// SQL Generation via OrderUtils
$sql = OrderUtils::buildSqlOrderBy($orderBy);
echo "SQL Order By: {$sql}\n";
// Output: ORDER BY `name` ASC, `age` DESC, `created_at` ASC

// Mongo Sort Array via OrderUtils
$mongoSort = OrderUtils::buildMongoSort($orderBy);
echo 'Mongo Sort: ' . json_encode($mongoSort) . "\n";
// Output: {"name":1,"age":-1,"created_at":1}

// --- 2. Using MySQLOrderBuilder Directly ---

echo "\n=== 2. Using MySQLOrderBuilder Directly ===\n";
$mysqlBuilder = new MySQLOrderBuilder();

// Standard SQL
$sqlDirect = $mysqlBuilder->build(['status' => 'DESC', 'priority' => 'ASC']);
echo "Direct SQL: {$sqlDirect}\n";
// Output: ORDER BY `status` DESC, `priority` ASC

// JSON Path Sorting
$jsonSql = $mysqlBuilder->buildJson('meta', '$.seo.score', 'DESC');
echo "JSON SQL: {$jsonSql}\n";
// Output: JSON_UNQUOTE(JSON_EXTRACT(`meta`, '$.seo.score')) DESC

// --- 3. Using MongoOrderBuilder Directly ---

echo "\n=== 3. Using MongoOrderBuilder Directly ===\n";
$mongoBuilder = new MongoOrderBuilder();

$mongoSortDirect = $mongoBuilder->build(['views' => 'DESC', 'title' => 'ASC']);
echo 'Direct Mongo Sort: ' . json_encode($mongoSortDirect) . "\n";
// Output: {"views":-1,"title":1}
