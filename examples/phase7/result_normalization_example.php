<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 10:00
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\Support\ResultNormalizer;
use MongoDB\BSON\ObjectId;

// 1. Basic Normalization (Converting Mongo ObjectId to string)
$mongoRow = [
    '_id' => new ObjectId(),
    'name' => 'Alice',
    'created_at' => '2023-01-01',
];

echo "--- Basic Normalization ---\n";
$normalized = ResultNormalizer::normalize($mongoRow);
// Output: ['id' => '...', 'name' => 'Alice', 'created_at' => '2023-01-01']
print_r($normalized);


// 2. Recursive Normalization
$nestedData = [
    [
        '_id' => new ObjectId(),
        'title' => 'Post 1',
        'author' => [
            '_id' => new ObjectId(),
            'name' => 'Bob'
        ]
    ],
    [
        '_id' => new ObjectId(),
        'title' => 'Post 2'
    ]
];

echo "\n--- Recursive Normalization ---\n";
// Create a configured instance
$normalizer = ResultNormalizer::create()
    ->recursive(true)
    ->strictIdTypes(true);

$normalizedList = $normalizer->normalizeAll($nestedData);
print_r($normalizedList);


// 3. Handling Mixed Input (e.g. from SQL join)
$sqlRow = [
    'id' => 123, // Integer ID
    'user_id' => '456', // String ID
    'meta' => null
];

echo "\n--- SQL Normalization ---\n";
// Default behavior preserves existing IDs
print_r(ResultNormalizer::normalize($sqlRow));
