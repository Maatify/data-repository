<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev DataRepository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-05 11:00:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Examples\Phase29;

use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Generic\Support\FilterParser;
use Maatify\DataRepository\Generic\Support\MySQLFilterBuilder;
use Maatify\DataRepository\Generic\Support\NormalizerOptions;

// 1. Basic Filtering (=)
$basicFilters = [
    'status' => 1,
    'type'   => 'admin',
];

// 2. Advanced Filtering (Operators)
$advancedFilters = [
    'age'        => ['>=' => 18],     // Range
    'created_at' => ['<' => '2025-01-01'],
    'name'       => ['LIKE' => '%test%'], // Partial match
    'role'       => ['IN' => [1, 2, 3]],  // List inclusion
];

// 3. Filtering with NULLs
$nullFilters = [
    'deleted_at' => null, // IS NULL
    'updated_at' => ['!=' => null], // IS NOT NULL
];

// 4. Using the FilterParser directly (for custom implementations)
$parser = new FilterParser();
$parsed = $parser->parse($advancedFilters);

echo "Parsed Filters:\n";
foreach ($parsed as $filter) {
    echo sprintf("Field: %s, Op: %s, Value: %s\n", $filter->field, $filter->operator, json_encode($filter->value));
}

// 5. Example Repository Usage (Pseudo-code)
/**
 * @template T of object
 * @extends GenericMySQLRepository<T>
 */
abstract class ExampleRepository extends GenericMySQLRepository
{
    public function getActiveUsers(): array
    {
        return $this->findBy([
            'status' => 1,
            'deleted_at' => null
        ]);
    }
}
