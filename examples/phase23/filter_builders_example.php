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

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\Support\FilterUtils;

// ==========================================
// Phase 23: Filter Builders Usage Example
// ==========================================

// This example demonstrates how the new Filter Builders are used via the FilterUtils facade.
// Although the logic has been extracted to MySQLFilterBuilder and MongoFilterBuilder,
// the public API remains consistent.

$filters = [
    'status' => 'active',
    'age'    => ['>' => 21, '<=' => 65],
    'roles'  => ['IN' => ['admin', 'editor']],
    'name'   => ['LIKE' => '%Smith%'],
];

echo "--- Input Filters ---\n";
print_r($filters);

// -----------------------------------------------------------------------------
// 1. SQL Generation (MySQL)
// -----------------------------------------------------------------------------
echo "\n--- MySQL SQL Generation ---\n";
[$sql, $params] = FilterUtils::buildSqlWhere($filters);

echo "SQL Clause: " . $sql . "\n";
echo "Parameters: \n";
print_r($params);

/*
Expected Output:
SQL Clause:  WHERE `status` = :status_EQ AND `age` > :age_GT AND `age` <= :age_LE AND `roles` IN (:roles_IN_0, :roles_IN_1) AND `name` LIKE :name_LIKE
Parameters: Array (...)
*/


// -----------------------------------------------------------------------------
// 2. MongoDB Query Generation
// -----------------------------------------------------------------------------
echo "\n--- MongoDB Query Generation ---\n";
$mongoQuery = FilterUtils::buildMongoFilter($filters);

print_r($mongoQuery);

/*
Expected Output:
Array
(
    [status] => active
    [age] => Array
        (
            [$gt] => 21
            [$lte] => 65
        )
    [roles] => Array
        (
            [$in] => Array
                (
                    [0] => admin
                    [1] => editor
                )
        )
    [name] => Array
        (
            [$regex] => %Smith%
            [$options] => i
        )
)
*/
