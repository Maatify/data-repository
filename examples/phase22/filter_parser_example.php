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

use Maatify\DataRepository\Generic\Support\FilterParser;
use Maatify\DataRepository\Generic\Support\FilterUtils;

// 1. Direct Usage of FilterParser (Internal Component)
echo "--- Direct FilterParser Usage ---\n";
$parser = new FilterParser();

// Define a complex filter set
$filters = [
    'status' => 'active',
    'age'    => ['>' => 18, '<' => 65],
    'role'   => ['IN' => ['admin', 'editor']],
    'bio'    => ['LIKE' => '%developer%'],
    'deleted_at' => null,
];

$parsedFilters = $parser->parse($filters);

foreach ($parsedFilters as $filter) {
    echo sprintf(
        "Field: %-10s | Operator: %-8s | Value: %s\n",
        $filter->field,
        $filter->operator,
        is_array($filter->value) ? json_encode($filter->value) : $filter->value
    );
}

// 2. Integration via FilterUtils (Public API)
echo "\n--- Integration via FilterUtils (SQL) ---\n";

try {
    [$sql, $params] = FilterUtils::buildSqlWhere($filters);
    echo "Generated SQL: " . $sql . "\n";
    echo "Parameters: " . json_encode($params, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "SQL Generation Error: " . $e->getMessage() . "\n";
}

echo "\n--- Integration via FilterUtils (Mongo) ---\n";

try {
    $mongoQuery = FilterUtils::buildMongoFilter($filters);
    echo "Generated Mongo Query: " . json_encode($mongoQuery, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Mongo Generation Error: " . $e->getMessage() . "\n";
}
