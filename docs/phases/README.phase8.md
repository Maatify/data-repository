# Phase 8: CRUD Edge Cases

[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

## Overview
This phase focused on ensuring robustness in the Generic CRUD layer by addressing edge cases such as NULL values, partial updates, and invalid data types. It also involved standardizing error messages by ensuring all driver-level exceptions are caught and rethrown as `RepositoryException`.

## Changes
- **Added Tests**:
    - `tests/Generic/EdgeCases/NullValuesTest.php`: Verifies correct handling of NULL values in INSERT and UPDATE operations.
    - `tests/Generic/EdgeCases/PartialUpdateTest.php`: Ensures that partial updates only modify the specified fields and that empty updates are handled gracefully.
    - `tests/Generic/EdgeCases/InvalidTypesTest.php`: Tests behavior when invalid data types (e.g., objects) are passed to repository methods, ensuring strict exception handling.

- **Refactored Code**:
    - **Exception Handling**: Updated `GenericMySQLRepository`, `GenericMongoRepository`, and `GenericRedisRepository` to wrap driver-specific calls (PDO, MongoDB, Redis) in try-catch blocks.
    - **Standardized Errors**: All methods now consistently throw `Maatify\DataRepository\Exceptions\RepositoryException` with context-aware messages (e.g., "Find failed: ...", "Insert failed: ...") instead of leaking raw driver exceptions.

## Technical Details

### Null Values
- `GenericMySQLRepository` correctly passes `null` to PDO, which translates to SQL `NULL`.
- Tests confirm that fields set to `null` in the input array are correctly bound and executed.

### Partial Updates
- `GenericMySQLRepository::update` constructs the `SET` clause dynamically based on the keys of the input array.
- If the input array is empty, the method returns `false` immediately, avoiding an invalid SQL query.
- `GenericRedisRepository` merges existing data with new data to simulate partial updates in a KV store.
- `GenericMongoRepository` uses `$set` operator for atomic partial updates.

### Invalid Types & Exceptions
- **MySQL**: Wraps `PDOException` in `RepositoryException`.
- **Mongo**: Wraps generic `Exception` (and driver specific ones) in `RepositoryException`.
- **Redis**: Wraps driver exceptions in `RepositoryException`.
- This standardization ensures that the upper layers of the application can handle errors uniformly without knowing the underlying driver details.

## Future Considerations
- As more complex types (arrays, objects) are supported, explicit serialization or casting layers (handled in future Hydration phases) will become more important.
- The `RepositoryException` hierarchy could be expanded (e.g., `EntityNotFoundException`, `DuplicateKeyException`) if more granular error handling is needed in future phases.
