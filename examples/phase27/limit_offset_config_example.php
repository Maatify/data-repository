<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\Pagination\LimitOffsetConfig;
use Maatify\DataRepository\Generic\Support\LimitOffsetValidator;
use Maatify\DataRepository\Exceptions\RepositoryException;

// Create a custom configuration for admin export (higher limits)
$exportConfig = LimitOffsetConfig::create()
    ->withMaxLimit(5000)
    ->withMaxOffset(0);

try {
    // This would fail with standard config if the limit was > 1000
    // But here we set maxLimit to 5000, so 2000 is allowed.
    $limit = 2000;
    $offset = 0;

    LimitOffsetValidator::validateWithConfig($limit, $offset, $exportConfig);

    echo "Validation passed for limit: $limit\n";

    $normalized = LimitOffsetValidator::validateAndNormalize($limit, $offset, $exportConfig);
    print_r($normalized);

} catch (RepositoryException $e) {
    echo 'Validation failed: ' . $e->getMessage() . "\n";
}
