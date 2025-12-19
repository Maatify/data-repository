# Phase 3 (BETA): Generic CRUD — Observed Behavior

## Status
BETA — Discovered from existing source code

## Constitutional Constraints
### ADR-001 (Scope Lock)
- **Identity**: Repository abstraction layer, not an ORM or Query Builder.
- **Redis**: Key-value/cache only. Blocking commands forbidden (though see warnings below).
- **Strictness**: `declare(strict_types=1)` and PHPStan Level Max required.

### ADR-014 (Backward Compatibility)
- **Versioning**: Strict Semantic Versioning.
- **Stability**: No breaking changes in v1.x public API.
- **Deprecation**: Must be explicit with `@deprecated` and announced.

### ADR-015 (Governance)
- **Release**: Manual approval required. `main` branch protected.
- **Compliance**: ADRs are the "supreme law".

## What Phase 3 Implements
### Supported Repositories
- `GenericMySQLRepository`: PDO-based implementation.
- `GenericMongoRepository`: MongoDB\Collection-based implementation.
- `GenericRedisRepository`: Redis key-value implementation with JSON serialization.

### CRUD Operations
All generic repositories implement `Maatify\DataRepository\Contracts\Repository\RepositoryInterface`:
- `find(int|string $id)`
- `findBy(array $filters, ...)`
- `findAll()`
- `findOneBy(array $filters)`
- `count(array $filters)`
- `insert(array $data)`
- `update(int|string $id, array $data)`
- `delete(int|string $id)`

### Pagination
- Implemented via `paginate()` and `paginateBy()`.
- Returns `PaginationResultDTO` containing hydrated data and metadata.

### Filtering & Sorting
- **MySQL**: Uses `FilterUtils` (semantic placeholders) and `OrderUtils` (SQL generation).
- **MongoDB**: Uses `FilterUtils` (array structure) and `OrderUtils` (options array).
- **Redis**: Uses in-memory filtering and sorting via `OrderUtils::sortArray`.

### Hydration
- Integrated via `RepositoryHydrationTrait`.
- Supports object mapping via `hydrator` context if configured.

## What Phase 3 Explicitly Does NOT Implement
- **Relations**: No JOINs, eager loading, or relationship management.
- **Query Builder**: No fluent API for constructing complex queries.
- **Secondary Indexes**: Redis does not use secondary indexes; it relies on key iteration.
- **Transactions**: No cross-repository transaction orchestration.

## Redis Behavior (Observed)
- **ID Requirement**: `insert()` throws exception if `id` is missing from payload; manual ID management required.
- **Filtering**: `findBy()` fetches **ALL** items matching the key prefix, then filters them in-memory using PHP logic.
- **Pagination**: Optimized to fetch keys first, slice keys in-memory, then fetch values only for the current page.
- **Sorting**: Performed in-memory after fetching data.

## Compatibility Notes
- **Exceptions**: All driver exceptions are caught and wrapped in `Maatify\DataRepository\Exceptions\RepositoryException`.
- **Typing**: Strict return types enforced. `find` returns `?array`, `findBy` returns `array`.
- **Generics**: Classes use `@template T` for static analysis support.

## Warnings & Ambiguities
- **Redis Performance**: `GenericRedisRepository` uses `$this->getRedisOps()->keys($prefix . '*')`. If `RedisOps` uses the blocking `KEYS` command instead of `SCAN`, this violates ADR-001 constraints for large datasets.
- **Redis Efficiency**: `findBy` on Redis loads the entire dataset into memory before filtering, which is unsafe for large collections.
