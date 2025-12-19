# Phase 23: Filter Builders (MySQL + Mongo)

[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](../../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

## Overview
Phase 23 introduces dedicated `MySQLFilterBuilder` and `MongoFilterBuilder` classes to handle the generation of driver-specific query structures. This refactoring extracts logic previously contained within the static `FilterUtils` class, promoting better separation of concerns and testability.

## Changes

### New Components
*   **`Maatify\DataRepository\Generic\Support\MySQLFilterBuilder`**: Responsible for converting `FieldFilter` objects (from `FilterParser`) into SQL `WHERE` clauses and parameter arrays.
*   **`Maatify\DataRepository\Generic\Support\MongoFilterBuilder`**: Responsible for converting `FieldFilter` objects into MongoDB query arrays, including operator mapping (`$gt`, `$regex`, etc.).

### Refactoring
*   **`Maatify\DataRepository\Generic\Support\FilterUtils`**: Now acts as a facade, delegating calls to the new builder classes. This maintains backward compatibility for existing consumers while using the new modular logic internally.

## Implementation Details

### MySQL Filter Builder
*   Handles SQL reserved word validation.
*   Generates semantic placeholders (e.g., `:age_GT`) for better debuggability.
*   Supports `IN`, `NOT IN`, `LIKE`, `BETWEEN`, `IS NULL`, and standard comparison operators.

### Mongo Filter Builder
*   Handles MongoDB reserved key validation (`$where`, `$match`, etc.).
*   Maps standard SQL-like operators (`>`, `LIKE`) to Mongo equivalents (`$gt`, `$regex`).
*   Automatically maps `id` to `_id`.
*   Merges multiple conditions for the same field (e.g., range queries).

## Testing
*   Added `tests/Generic/Filters/MySQLFilterBuilderTest.php`: Verifies SQL string generation and parameter binding.
*   Added `tests/Generic/Filters/MongoFilterBuilderTest.php`: Verifies Mongo array structure generation.

## Usage Example

```php
use Maatify\DataRepository\Generic\Support\FilterUtils;

// Usage remains the same via FilterUtils facade
$filters = [
    'status' => 1,
    'age' => ['>' => 18, '<' => 65],
    'name' => ['LIKE' => '%John%']
];

// SQL
[$where, $params] = FilterUtils::buildSqlWhere($filters);
// $where: " WHERE `status` = :status_EQ AND `age` > :age_GT AND `age` < :age_LT AND `name` LIKE :name_LIKE"

// Mongo
$query = FilterUtils::buildMongoFilter($filters);
// $query: ['status' => 1, 'age' => ['$gt' => 18, '$lt' => 65], 'name' => ['$regex' => '%John%', '$options' => 'i']]
```
