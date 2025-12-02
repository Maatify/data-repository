<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-01
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\Support\FilterUtils;

// Example 1: Simple Equality
echo "--- Simple Equality ---\n";
$filters = ['status' => 'active'];
[$sql, $params] = FilterUtils::buildSqlWhere($filters);

echo 'SQL: ' . $sql . "\n";
// Output: SQL:  WHERE `status` = :status
echo 'Params: ' . print_r($params, true) . "\n";
// Output: Params: Array ( [status] => active )

// Example 2: Complex Conditions with Operators
echo "\n--- Complex Conditions ---\n";
$filters = [
    'age' => ['>' => 18],
    'role' => ['IN' => ['admin', 'editor']],
    'deleted_at' => null
];
[$sql, $params] = FilterUtils::buildSqlWhere($filters);

echo 'SQL: ' . $sql . "\n";
// Output: SQL:  WHERE `age` > :age_GT AND `role` IN (:role_IN_0, :role_IN_1) AND `deleted_at` IS NULL
echo 'Params: ' . print_r($params, true) . "\n";

/*
Params: Array
(
    [age_GT] => 18
    [role_IN_0] => admin
    [role_IN_1] => editor
)
*/
