# Phase 25: Order Builders (MySQL + Mongo)

## Overview
Phase 25 continues the refactoring process initiated in Phases 22-24 by extracting driver-specific sorting logic from `OrderUtils` into dedicated builder classes. This improves maintainability, testability, and separation of concerns.

## Changes
- **Added `Maatify\DataRepository\Generic\Support\MySQLOrderBuilder`**: Handles generation of SQL `ORDER BY` clauses and MySQL-specific JSON sorting.
- **Added `Maatify\DataRepository\Generic\Support\MongoOrderBuilder`**: Handles generation of MongoDB sort arrays.
- **Refactored `Maatify\DataRepository\Generic\Support\OrderUtils`**: Now acts as a lightweight facade that delegates actual logic to the new builder classes.

## Components

### MySQLOrderBuilder
Responsible for constructing SQL strings from normalized order arrays. It includes:
- Validation of column names to prevent SQL injection.
- Quoting of identifiers (supports table.column syntax).
- `buildJson()` method for handling MySQL JSON path sorting (`JSON_UNQUOTE(JSON_EXTRACT(...))`).

### MongoOrderBuilder
Responsible for converting normalized order arrays into MongoDB-compatible sort arrays (mapping `ASC` to `1` and `DESC` to `-1`).

## Testing
- Added `tests/Generic/Ordering/MySQLOrderBuilderTest.php` covering standard SQL generation and JSON path handling.
- Added `tests/Generic/Ordering/MongoOrderBuilderTest.php` covering MongoDB sort array structure and direction normalization.
- Existing tests for `OrderUtils` remain valid as the public API has not changed.
