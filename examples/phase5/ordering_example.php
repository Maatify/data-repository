<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 05:25
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\Support\OrderUtils;

// 1. Basic Normalization
$orderBy = ['name' => 'asc', 'age' => 'DESC', 'invalid_col' => 'foo'];
$normalized = OrderUtils::normalize($orderBy);
print_r($normalized);
// Output: Array ( [name] => ASC [age] => DESC [invalid_col] => ASC )

// 2. SQL Order By Generation
$sqlOrder = OrderUtils::buildSqlOrderBy(['users.name' => 'asc', 'created_at' => 'desc']);
echo 'SQL: ' . $sqlOrder . "\n";
// Output: SQL: ORDER BY `users`.`name` ASC, `created_at` DESC

// 3. Mongo Sort Generation
$mongoSort = OrderUtils::buildMongoSort(['score' => 'desc']);
print_r($mongoSort);
// Output: Array ( [score] => -1 )

// 4. In-Memory Array Sorting
$data = [
    ['id' => 1, 'name' => 'Charlie', 'age' => 25],
    ['id' => 2, 'name' => 'Alice',   'age' => 30],
    ['id' => 3, 'name' => 'Bob',     'age' => 20],
];

// Sort by Age ASC
$sortedByAge = OrderUtils::sortArray($data, ['age' => 'asc']);
print_r($sortedByAge);

// Sort by Name DESC
$sortedByName = OrderUtils::sortArray($data, ['name' => 'desc']);
print_r($sortedByName);

// 5. String Parsing (e.g., from URL query param)
$stringInput = 'name:asc,age:desc';
$parsed = OrderUtils::fromString($stringInput);
print_r($parsed);
// Output: Array ( [name] => ASC [age] => DESC )

// 6. JSON Column (MySQL)
$jsonOrder = OrderUtils::buildJsonOrderBy('meta', 'user.rank', 'desc');
echo 'JSON SQL: ' . $jsonOrder . "\n";
// Output: JSON SQL: JSON_UNQUOTE(JSON_EXTRACT(`meta`, 'user.rank')) DESC
