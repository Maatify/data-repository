<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-04 10:10
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Examples\Phase10;

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\Common\Pagination\DTO\PaginationDTO;
use Maatify\DataRepository\Pagination\PaginationContext;
use Maatify\DataRepository\Pagination\PaginationEntry;

// 1. Create Pagination Entry (Input)
$entry = new PaginationEntry(page: 2, perPage: 20);

echo 'Page: ' . $entry->getPage() . "\n";
echo 'Per Page: ' . $entry->getPerPage() . "\n";
echo 'Offset: ' . $entry->getOffset() . "\n";

// 2. Use Context to hold metadata
$context = new PaginationContext();
$context->setEntry($entry);

// 3. Simulate Meta Calculation (Output)
// Constructor: int $page, int $perPage, int $count, int $totalPages, bool $hasNext, bool $hasPrev
$meta = new PaginationDTO(2, 20, 100, 5, true, true);
$context->setMeta($meta);

echo 'Total Pages: ' . $context->getMeta()->totalPages . "\n";
