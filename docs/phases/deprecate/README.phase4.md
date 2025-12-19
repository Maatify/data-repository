# Phase 4: Advanced Filtering
[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](../../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

## Status: Completed
**Version:** 1.0.1
**Timestamp:** 2025-11-25 04:30:00+02:00

## Summary
Added advanced filtering capabilities including `IN`, `NOT IN`, `LIKE`, `BETWEEN`, `IS NULL`, and range operators (`>`, `<`, `>=`, `<=`) for MySQL and MongoDB repositories.

## Changes
- **Added:** `src/Generic/Support/FilterUtils.php` - A centralized utility for parsing filter arrays into SQL WHERE clauses (for PDO) and MongoDB filter arrays.
- **Updated:** `GenericMySQLRepository` to use `FilterUtils::buildSqlWhere`.
- **Updated:** `GenericMongoRepository` to use `FilterUtils::buildMongoFilter`.

## Supported Filters

### SQL & Mongo Common Syntax

```php
// Equality
['status' => 'active']

// IN Array
['status' => ['IN' => ['active', 'pending']]]
['status' => ['NOT IN' => ['banned', 'deleted']]]

// Range
['age' => ['>' => 18]]
['price' => ['<=' => 100.00]]
['score' => ['!=' => 0]]

// LIKE (Partial Match)
// SQL: LIKE %John%
// Mongo: Regex /.*John.*/i
['name' => ['LIKE' => '%John%']]

// BETWEEN
['age' => ['BETWEEN' => [18, 65]]]

// IS NULL / IS NOT NULL
['deleted_at' => ['IS NULL' => true]]
['deleted_at' => ['IS NOT NULL' => true]]
// OR simplified for equality:
['deleted_at' => null] // IS NULL
```

## Security
- `FilterUtils` strictly validates column names to prevent SQL injection.
- Reserved words (e.g., `SELECT`, `DROP`) are forbidden as column names.
- All values are bound as parameters in PDO to prevent injection.

## Tests
- Added `tests/Generic/Fake/Filters/AdvancedFilterFakeTest.php` covering 100% of the logic.
