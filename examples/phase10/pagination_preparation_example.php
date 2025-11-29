<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify DataRepository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 11:15:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Maatify\Common\Pagination\DTO\PaginationDTO;
use Maatify\DataRepository\Pagination\PaginationContext;
use Maatify\DataRepository\Pagination\PaginationEntry;

// 1. Setup Pagination Entry (Request)
// Normally comes from Controller/Request
$page = 2;
$perPage = 25;
$entry = new PaginationEntry($page, $perPage);

echo "--- Pagination Request ---\n";
echo "Page: " . $entry->getPage() . "\n";
echo "Per Page: " . $entry->getPerPage() . "\n";
echo "Offset: " . $entry->getOffset() . "\n";

// 2. Setup Pagination Context
// Used to pass pagination state through repository layers
$context = new PaginationContext();
$context->setEntry($entry);

if ($context->hasEntry()) {
    echo "\nContext has entry.\n";
}

// 3. Simulate Repository Operation (e.g. Count + Fetch)
$totalRecords = 150;
$totalPages = (int)ceil($totalRecords / $perPage);

// Create Meta DTO from Maatify/Common
$meta = new PaginationDTO(
    page: $entry->getPage(),
    perPage: $entry->getPerPage(),
    total: $totalRecords,
    totalPages: $totalPages,
    hasNext: $entry->getPage() < $totalPages,
    hasPrev: $entry->getPage() > 1
);

// Store Meta in Context
$context->setMeta($meta);

// 4. Output Result
if ($context->hasMeta()) {
    echo "\n--- Pagination Metadata (Calculated) ---\n";
    print_r($context->getMeta()->toArray());
}
