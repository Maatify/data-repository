# Phase 5: Ordering & Sorting

## Overview
This phase introduces a unified way to handle sorting (`ORDER BY`) across all repositories. It ensures that sorting logic is consistent, safe, and normalized regardless of the underlying driver (MySQL, MongoDB, Redis/Array).

## Changes

### 1. New Utility: `OrderUtils`
Located at `Maatify\DataRepository\Generic\Support\OrderUtils`.

**Features:**
- **Normalization**: Converts user input into safe `['col' => 'ASC/DESC']` format.
- **SQL Generation**: Builds standard `ORDER BY` clauses with backtick quoting.
- **JSON Support**: specialized `buildJsonOrderBy` for MySQL JSON paths.
- **Mongo Generation**: Converts to `['col' => 1/-1]`.
- **Array Sorting**: `usort` wrapper for in-memory sorting (Redis/Fakes).
- **String Parsing**: Parses `col:asc,col2:desc` strings (useful for API query params).

### 2. Generic Repository Updates
- **MySQL**: `findBy()` now supports multi-column sorting via `OrderUtils::buildSqlOrderBy`.
- **Mongo**: `findBy()` now supports multi-column sorting via `OrderUtils::buildMongoSort`.
- **Redis**: Updated to import utilities, but remains primarily Key-Value. `findAll()` returns unsorted results by default unless in-memory sorting is applied manually.

## Usage Examples

```php
<?php

use Maatify\DataRepository\Generic\Support\OrderUtils;

// 1. Basic Normalization
$orderBy = ['name' => 'asc', 'age' => 'DESC', 'invalid_col' => 'foo'];
$normalized = OrderUtils::normalize($orderBy);
// Result: ['name' => 'ASC', 'age' => 'DESC', 'invalid_col' => 'ASC']


// 2. SQL Order By Generation
$sqlOrder = OrderUtils::buildSqlOrderBy(['users.name' => 'asc', 'created_at' => 'desc']);
// Result: "ORDER BY `users`.`name` ASC, `created_at` DESC"


// 3. Mongo Sort Generation
$mongoSort = OrderUtils::buildMongoSort(['score' => 'desc']);
// Result: ['score' => -1]


// 4. In-Memory Array Sorting
$data = [
    ['id' => 1, 'name' => 'Charlie', 'age' => 25],
    ['id' => 2, 'name' => 'Alice',   'age' => 30],
    ['id' => 3, 'name' => 'Bob',     'age' => 20],
];

// Sort by Age ASC
$sortedByAge = OrderUtils::sortArray($data, ['age' => 'asc']);

// Sort by Name DESC
$sortedByName = OrderUtils::sortArray($data, ['name' => 'desc']);


// 5. String Parsing (e.g. from URL query param)
$stringInput = "name:asc,age:desc";
$parsed = OrderUtils::fromString($stringInput);
// Result: ['name' => 'ASC', 'age' => 'DESC']


// 6. JSON Column (MySQL)
$jsonOrder = OrderUtils::buildJsonOrderBy('meta', 'user.rank', 'desc');
// Result: "JSON_UNQUOTE(JSON_EXTRACT(`meta`, 'user.rank')) DESC"
```
