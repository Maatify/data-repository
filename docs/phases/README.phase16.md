# Phase 16: Adapter-Specific Pagination Optimization

## Overview
Phase 16 focuses on optimizing pagination logic for each supported driver to ensure performance and efficiency with large datasets.

## Optimizations Implemented

### 1. MySQL (PDO)
- **Standard SQL**: Utilizes `LIMIT` and `OFFSET` clauses directly in the SQL query.
- **Efficiency**: Avoids fetching all rows into PHP memory. The database engine handles the slicing.
- **Verification**: Tests confirm `LIMIT` and `OFFSET` are present in generated queries.

### 2. MongoDB
- **Native Options**: Utilizes `limit` and `skip` options in `MongoDB\Collection::find()`.
- **Efficiency**: Cursor-based pagination handled by the MongoDB server.
- **Verification**: Tests confirm options array passed to the driver contains correct `limit` and `skip` values.

### 3. Redis (Key-Value Store)
- **Lazy Loading**:
  - Previously: `findAll()` fetched *all* values, decoded them, and then sliced the array in memory.
  - **Optimized**:
    1. Fetches all matching *keys* first (lightweight).
    2. Slices the array of *keys* in memory.
    3. Fetches *only* the values for the keys in the current page.
- **Benefit**: Significantly reduces network overhead and memory usage when storing large JSON payloads, as only the required page's data is retrieved.
- **Note**: Strict `SCAN` cursor pagination is not implemented for generic `keys*` patterns due to complexity in random access page jumping, but the current approach is a massive improvement over `O(N)` full-value fetching.

## Usage
No changes to the public API. `paginate()` and `paginateBy()` continue to return `PaginationResultDTO`.

```php
// MySQL / Mongo / Redis
$result = $repo->paginate($page = 2, $perPage = 25);

// $result->data contains only 25 items.
// $result->pagination contains metadata (total, pages, etc).
```

## Files Created
- `tests/Pagination/Optimization/MySQLPaginationOptimizationTest.php`
- `tests/Pagination/Optimization/MongoPaginationOptimizationTest.php`
- `tests/Pagination/Optimization/RedisPaginationOptimizationTest.php`
- `examples/phase16/optimized_pagination_example.php`
