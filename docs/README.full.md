# Maatify Data Repository — Full Documentation (Initial)

This full documentation file will expand across phases. For Phase 2 it captures the base repository layer foundations.

## Architecture Overview
- Repositories depend on `Maatify\Common\Contracts\Adapter\AdapterInterface` to ensure adapter abstraction.
- Base repositories expose normalized driver accessors for MySQL (PDO/DBAL), MongoDB (Database/Collection), and Redis (phpredis/Predis).
- Exceptions are centralized via `RepositoryException` for adapter validation and missing driver scenarios.

## Implemented Base Classes
- `BaseRepository` — shared adapter storage and validation helpers.
- `BaseMySQLRepository` — accessors for PDO and Doctrine DBAL connections.
- `BaseMongoRepository` — accessors for MongoDB databases and collections.
- `BaseRedisRepository` — accessors for phpredis and Predis clients.

## Error Handling
- `RepositoryException::forInvalidAdapter()` standardizes adapter/driver mismatch errors.
- `RepositoryException::forMissingDriver()` standardizes missing or disconnected driver errors.

## Upcoming Work
- Generic repositories with CRUD, pagination, filtering, validation, and hydration layers (Phase 3 onward).
- Expanded testing matrix covering fake vs real adapters for all drivers.
