# Phase 3: Generic Repository Implementations

[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](../../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)


## Status: COMPLETED
**Date:** 2025-11-25
**Version:** 1.0.0 (Dev)

## Summary
Implemented concrete "Generic" repositories for MySQL, MongoDB, and Redis **plus lightweight Support Ops wrappers** around the low-level drivers. These classes provide out-of-the-box CRUD operations (Create, Read, Update, Delete) so developers don't need to write SQL or Mongo queries for standard operations, while keeping driver-specific behavior encapsulated.

Generic repositories are built **on top of the Base layer** and expose a small, consistent API at a high level:

- `find(int|string $id): ?array`
- `findBy(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array` *(MySQL & MongoDB only)*
- `findOneBy(array $filters): ?array` *(MySQL & MongoDB only)*
- `findAll(): array`
- `count(array $filters = []): int`
- `insert(array $data): int|string`
- `update(int|string $id, array $data): bool`
- `delete(int|string $id): bool`

Low-level interactions with PDO, MongoDB, and Redis/Predis are normalized through dedicated **Ops helpers**:

- `MysqlOps` for PDO / DBAL-native connections
- `MongoOps` for MongoDB collections
- `RedisOps` for Redis / Predis / FakeRedis drivers

## Deliverables
- **MySQL**:
    - `GenericMySQLRepository` (supports `find`, `findBy`, `findOneBy`, `findAll`, `count`, `insert`, `update`, `delete` using PDO/DBAL under the hood).
    - `MysqlOps` (thin helper around the PDO/DBAL-native driver, exposed via `getMysqlOps()` for advanced extensions).
- **MongoDB**:
    - `GenericMongoRepository` (supports standard BSON queries and ObjectId handling for `find`, `findBy`, `findOneBy`, `findAll`, `count`, `insert`, `update`, `delete`).
    - `MongoOps` (thin helper around `MongoDB\Collection`, exposed via `getMongoOps()` for internal utilities and advanced consumers).
- **Redis**:
    - `GenericRedisRepository` (Key-Value JSON storage implementation with `find`, `insert`, `update`, `delete`, `findAll`, `count`; filtering-style operations are intentionally restricted, see notes below).
    - `RedisOps` (normalization layer that unifies Redis, Predis, and FakeRedis behavior for `get`, `set`, `del`, and `keys`, used internally by `GenericRedisRepository`).

## Usage

### MySQL
```php
class UserRepository extends GenericMySQLRepository {
    protected string $tableName = 'users';
}

$repo->find(1);
$repo->findBy(['status' => 'active'], ['created_at' => 'DESC']);
$repo->insert(['name' => 'John', 'email' => 'john@example.com']);
```

### MongoDB
```php
class LogRepository extends GenericMongoRepository {
    protected string $collectionName = 'logs';
}

$repo->insert(['level' => 'error', 'msg' => 'Test']);
$repo->findBy(['level' => 'error']);
```

### Redis
```php
class CacheRepository extends GenericRedisRepository {
    protected string $keyPrefix = 'cache:';
}

$repo->insert(['id' => 'user_1', 'data' => '...']); // Key: cache:user_1
$repo->find('user_1');
```

## Technical Notes

- **Testing Strategy**:
    - **Fake Tests**: Use `sqlite::memory:` for MySQL and mock collections / fake adapters for Mongo and Redis to verify generic logic deterministically.
    - **Real Tests**: Attempt connections to Docker services for MySQL, MongoDB, Redis, DBAL, and Predis. If connectivity or authentication fails (for example, real Mongo in CI), tests are **skipped** rather than failing the build.

- **Filtering**:
    - Currently supports simple "equals" filtering via associative arrays, e.g. `['status' => 'active']`.
    - Ordering, `limit`, and `offset` are exposed as scalar arguments on `findBy()` rather than via a dedicated `PaginationDTO`.
    - A future phase may introduce first-class `PaginationDTO` and richer filter utilities from `maatify/common`; for now, keep filters simple.

- **Validator Integration**:
    - The roadmap mentions `Validator` integration. At this phase, the Generic repositories **do not yet depend on a Validator service**; validation is limited to basic type/shape checks inside each method (e.g. ensuring Redis payloads include an `id`).

- **Redis Limitations**:
    - `findBy()` and `findOneBy()` are intentionally **not supported** for Redis and throw `RepositoryException`, since Redis in this package is treated as a key–value store, not a query engine.
    - `findAll()` and `count()` operate by scanning keys using the configured `keyPrefix` and decoding JSON payloads.
