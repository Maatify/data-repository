# Phase 6: Limits & Offsets

## Summary
This phase implements unified `limit` and `offset` support across MySQL and Mongo repositories, with strict adapter-safe bounds validation.

## Changes
- **Added `LimitOffsetValidator`**: Centralized logic ensuring `limit > 0` and `offset >= 0`.
- **MySQL Integration**: `GenericMySQLRepository::findBy` now validates inputs before query generation.
- **Mongo Integration**: `GenericMongoRepository::findBy` now validates inputs before query execution.
- **Redis Note**: `GenericRedisRepository::findBy` continues to throw exception as it is a key-value store; `findAll` returns all items (for now).

## Usage

```php
// Find 10 users, skipping first 5
$users = $repo->findBy(['status' => 'active'], ['name' => 'ASC'], 10, 5);

// Validation
$repo->findBy([], null, -1); // Throws RepositoryException("Limit must be a positive integer.")
```

## Files Created/Modified
- `src/Generic/Support/LimitOffsetValidator.php`
- `src/Generic/GenericMySQLRepository.php`
- `src/Generic/GenericMongoRepository.php`
- `tests/Generic/LimitOffset/LimitOffsetValidatorTest.php`
- `tests/Generic/LimitOffset/GenericMySQLRepositoryLimitTest.php`
- `tests/Generic/LimitOffset/GenericMongoRepositoryLimitTest.php`
