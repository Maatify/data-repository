<?php

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\Support\NormalizerOptions;
use Maatify\DataRepository\Generic\Support\ResultNormalizer;

// Create options configuration
$options = NormalizerOptions::create()
    ->withKeepMongoId(true)
    ->withRecursive(true)
    ->withStrictIdTypes(false);

// Sample data with MongoDB convention
$data = [
    '_id' => 101,
    'username' => 'user_101',
    'meta' => [
        'created_at' => '2025-01-01',
        'tags' => ['admin', 'editor']
    ]
];

// Normalize using the options object
$normalized = ResultNormalizer::normalizeWithOptions($data, $options);

echo "Normalized Result:\n";
print_r($normalized);

// Output will preserve '_id' and handle nested 'meta' normalization if needed
