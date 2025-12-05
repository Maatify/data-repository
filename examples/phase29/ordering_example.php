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

use Maatify\DataRepository\Generic\Support\OrderParser;

// 1. Basic Ordering (ASC default)
$orderBy = ['created_at' => 'DESC'];

// 2. Multiple Column Ordering
$multiOrder = [
    'priority' => 'DESC',
    'created_at' => 'ASC',
];

// 3. Using the OrderParser
$parser = new OrderParser();
$parsed = $parser->parse($multiOrder, true); // true = strict validation

echo "Parsed Order:\n";
foreach ($parsed as $field) {
    echo sprintf("Field: %s, Direction: %s\n", $field->field, $field->direction);
}

// 4. JSON Path Ordering (MySQL specific feature)
// Requires MySQL 5.7+ and proper column support
$jsonOrder = [
    'meta->sort_order' => 'ASC',
];

// Note: Repository methods accept orderBy arrays directly.
// $repo->findAll($jsonOrder);
