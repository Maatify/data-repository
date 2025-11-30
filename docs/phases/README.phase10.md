# Phase 10: Pagination Hooks (Preparation Only)

## Overview
This phase sets up the foundational structures for pagination within the repository system. It introduces `PaginationEntry` for request handling and utilizes `Maatify\Common\Pagination\DTO\PaginationDTO` for metadata. `PaginationContext` is used to hold the pagination state throughout the execution pipeline.

## Changes

### Added
- **PaginationEntry**: Value object encapsulating pagination request parameters (page, perPage).
- **PaginationContext**: Container class to hold pagination state (entry and meta).

### External Dependencies
- **Maatify\Common\Pagination\DTO\PaginationDTO**: Used for pagination response metadata.

## Usage

### PaginationEntry
```php
use Maatify\DataRepository\Pagination\PaginationEntry;

$entry = new PaginationEntry(page: 2, perPage: 20);
$offset = $entry->getOffset(); // 20
```

### PaginationContext
```php
use Maatify\DataRepository\Pagination\PaginationContext;
use Maatify\Common\Pagination\DTO\PaginationDTO;

$context = new PaginationContext();
$context->setEntry($entry);

// Setting metadata (e.g. after query execution)
$meta = new PaginationDTO(page: 2, perPage: 20, total: 100, totalPages: 5, hasNext: true, hasPrev: true);
$context->setMeta($meta);
```
