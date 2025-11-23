# Phase 2 — Base Repository Layer

## Goals
- Implement core base repository classes for MySQL, MongoDB, and Redis drivers.
- Normalize driver access for PDO, Doctrine DBAL, MongoDB, phpredis, and Predis.
- Extend the repository exception surface for consistent driver validation errors.

## Delivered
- Added `BaseRepository` with shared adapter guards and driver assertions.
- Added `BaseMySQLRepository` exposing PDO and Doctrine DBAL accessors.
- Added `BaseMongoRepository` exposing Database and Collection helpers.
- Added `BaseRedisRepository` exposing phpredis and Predis helpers.
- Updated `RepositoryException` with adapter/driver error helpers.
- Initialized `README.full.md`, updated `README.md`, appended CHANGELOG, and generated `api-map.json`.

## Testing
- Not run in this phase (class scaffolding and documentation only).

## Next Steps
- Implement concrete generic repositories (CRUD, pagination, filtering) in Phase 3.
- Add hydration hooks and DTO integration.
- Expand tests to cover fake vs real adapters across all drivers.
