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

namespace Maatify\DataRepository\Examples\Phase24;

use Maatify\DataRepository\Generic\Support\OrderParser;
use Maatify\DataRepository\Generic\Support\OrderUtils;

require __DIR__ . '/../../../vendor/autoload.php';

// Example: Using OrderParser directly
$parser = new OrderParser();

$input = [
    'name' => 'ASC',
    'age'  => 'DESC',
    'invalid_column!@#' => 'ASC', // Will be sanitized
    'score' => 'invalid_direction', // Will fallback to ASC
];

echo "--- OrderParser Output ---\n";
$fields = $parser->parse($input);
foreach ($fields as $field) {
    echo "Field: {$field->field}, Direction: {$field->direction}\n";
}

// Example: Using OrderUtils (Backward Compatible)
echo "\n--- OrderUtils Output (Normalized) ---\n";
$normalized = OrderUtils::normalize($input);
print_r($normalized);

// Example: Strict Mode (Throws Exception)
try {
    echo "\n--- Strict Mode Test ---\n";
    $parser->parse(['score' => 'invalid'], true);
} catch (\InvalidArgumentException $e) {
    echo "Caught expected exception: " . $e->getMessage() . "\n";
}
