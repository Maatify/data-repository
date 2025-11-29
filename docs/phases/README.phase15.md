# Phase 15: Pagination Core (Hydration-Aware)

## Overview
This phase implements core pagination capabilities in the Generic Repository layer. It introduces standard `paginate()` and `paginateBy()` methods that return a standardized `PaginationResultDTO`, containing both the data array and a `PaginationDTO` metadata object (from `maatify/common`).

## Features
- **Standardized Output**: Returns `PaginationResultDTO` wrapping data and metadata.
- **Generic Implementation**: Added to `GenericMySQLRepository`, `GenericMongoRepository`, and `GenericRedisRepository`.
- **Filtering Support**: `paginateBy()` allows filtering alongside pagination (SQL/Mongo).
- **Redis Support**: Basic in-memory slicing for Redis pagination (Note: `paginateBy` throws exception for Redis as it lacks secondary indexes).

## Usage

### Basic Pagination
```php
$page = 1;
$perPage = 20;
$result = $repository->paginate($page, $perPage);

// Access Data
foreach ($result->data as $item) {
    // ...
}

// Access Metadata
echo $result->pagination->total; // Total items
echo $result->pagination->pages; // Total pages
```

### Pagination with Filters (SQL/Mongo)
```php
$filters = ['status' => 'active'];
$result = $repository->paginateBy($filters, 1, 20, ['created_at' => 'DESC']);
```

## Classes Added
- `Maatify\DataRepository\Pagination\PaginationResultDTO`
- `Maatify\DataRepository\Pagination\PaginationEntry` (Restored)
- `Maatify\DataRepository\Pagination\PaginationContext` (Restored)

## Changes
- Updated Generic Repositories to include `paginate` methods.
- Integration with `maatify/common` `PaginationDTO`.
