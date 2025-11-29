# 📘 Changelog

All notable changes to **maatify/data-repository** will be documented in this file.

The format is based on **[Keep a Changelog](https://keepachangelog.com/en/1.0.0/)**  
and the project adheres to **[Semantic Versioning](https://semver.org/spec/v2.0.0.html)**.

---

## [Unreleased]

### 🚀 Added
- **Pagination Hooks (Phase 10)**
    - Introduced `PaginationEntry` for encapsulating pagination request parameters (page, perPage).
    - Introduced `PaginationContext` for maintaining pagination state across the pipeline.
    - Integrated `Maatify\Common\Pagination\DTO\PaginationDTO` for standardized pagination metadata.

---

## [1.0.2] — 2025-11-27 (Phase 9 Update)

### 🚀 Added
- **Generic Ops Integration (Phase 9)**
    - Introduced `MysqlOps` normalization wrapper for PDO/DBAL driver quirks (e.g., `lastInsertId` type unification).
    - Introduced `MongoOps` normalization wrapper for MongoDB Collection (BSON `ObjectId` casting, cursor iteration).
    - Introduced `RedisOps` normalization wrapper for Redis/Predis/Fake unified KV operations.
    - Centralized driver-level logic in `src/Generic/Support/` to reduce duplication in Generic Repositories.
    - Updated `GenericMySQLRepository`, `GenericMongoRepository` to use new Ops classes.
    - Added comprehensive unit tests for all Ops classes.
    - Included `examples/phase9/ops_usage_example.php` demonstrating advanced Ops usage.

---

## [1.0.1] — 2025-11-25 (Phase 8 Update)

### 🚀 Added
- **CRUD Edge Cases (Phase 8)**
    - Standardized error handling: All Generic Repository methods now wrap driver operations in `try-catch` blocks and throw `RepositoryException` for consistency.
    - Verified handling of `NULL` values in MySQL/PDO operations.
    - Verified partial update logic: `update()` only modifies specified fields and returns `false` (no-op) for empty data arrays.
    - Added comprehensive tests for edge cases: `NullValuesTest`, `PartialUpdateTest`, `InvalidTypesTest`.

---

## [1.0.2] — 2025-11-25

### 🚀 Added
- **Result Normalization (Phase 7)**
    - Implemented `ResultNormalizer` for consistent data structure across drivers.
    - Added automatic `_id` to `id` mapping for MongoDB.
    - Added `ObjectId` to string casting.
    - Added recursive normalization for nested structures.
    - Added fluent API for configuration (`ResultNormalizer::create()->recursive()->strictIdTypes()`).

- **Limits & Offsets (Phase 6)**
    - Implemented unified `limit` and `offset` support across all Generic repositories.
    - Added `LimitOffsetValidator` providing strict, centralized validation 
      **(Limit: 1–10,000, Offset: 0–100,000)**.
    - `GenericMySQLRepository::findBy` (SQL `LIMIT` / `OFFSET`)
    - `GenericMongoRepository::findBy` (MongoDB `limit` / `skip`)
    - Added Redis behavior:
      `GenericRedisRepository::findBy` now **throws `RepositoryException`** (unsupported for now).
    - Added full test coverage for all validator paths and repository interactions.
    - Included usage examples under `examples/phase6/`.

---


### 🔧 Changed
- **Validation**
    - Enforced strict validation for limit and offset parameters in all Generic repositories.

---

## [1.0.1] — 2025-11-25

### 🚀 Added
- **Ordering & Sorting (Phase 5)**
    - Implemented `OrderUtils` for centralized sorting logic (SQL, Mongo, Array, JSON).
    - Updated `GenericMySQLRepository` to support multi-column `orderBy`.
    - Updated `GenericMongoRepository` to support normalized Mongo sorting.
    - Added comprehensive tests and `examples/phase5/ordering_example.php`.

- **Advanced Filtering (Phase 4)**
    - Implemented `FilterUtils` for centralized filter parsing across SQL and NoSQL drivers.
    - Added support for SQL/Mongo operators: `IN`, `NOT IN`, `LIKE`, `BETWEEN`, `IS NULL`, `IS NOT NULL`.
    - Added support for range operators: `>`, `<`, `>=`, `<=`, `!=`.
    - Added comprehensive tests ensuring 100% coverage of filtering logic.
    - Added usage examples in `examples/phase4/`.

### 🔧 Changed
- **Strict Validation**
    - `GenericMySQLRepository::findBy` now strictly validates column names and throws `InvalidArgumentException` for invalid or reserved columns (previously ignored).
    - `GenericMongoRepository` now normalizes filters via `FilterUtils`.

---

## [1.0.0] — 2025-11-25

### 🚀 Added
- **Base Layers**
    - Added abstract `BaseRepository`, `BaseMySQLRepository`, `BaseMongoRepository`, and `BaseRedisRepository`.

- **Generic Repository Layer (Phase 3)**
    - Added full Generic CRUD layer for MySQL, MongoDB, and Redis.
    - Added `GenericMySQLRepository` with unified SQL CRUD operations.
    - Added `GenericMongoRepository` with BSON filtering and ObjectId support.
    - Added `GenericRedisRepository` with JSON key–value CRUD and prefix scanning.
    - Introduced a consistent CRUD API: `find`, `findBy`, `findOneBy`, `findAll`, `count`, `insert`, `update`, `delete`.
    - Implemented simple associative-array filtering, order, limit, and offset for MySQL/Mongo.
    - Added Redis-specific restrictions where `findBy` / `findOneBy` throw `RepositoryException`.
    - Added Fake + Real tests covering all Generic repositories with CI-safe skip logic.

- **Normalization**
    - Implemented unified normalization logic for `PDO`/`DBAL` and `Redis`/`Predis` adapters.

- **Validation**
    - Added `validateAdapter()` ensuring strict repository–adapter compatibility.

- **Core Components**
    - Added `RepositoryResolver` for adapter routing.
    - Added `RepositoryException` for unified error handling.
    - Added `RepositoryLogger` with contextual tagging (`source: maatify/data-repository`).

- **Configuration**
    - Added `composer.json`, `phpunit.xml.dist`, and `phpstan.neon`.

- **Tests**
    - Added Fake adapter compatibility tests (`tests/Base/Fake/*`).
    - Added Real adapter integration tests (`tests/Base/Real/*`) using `maatify/data-adapters`.
    - Added `RealAdapterTrait`.
    - Added `FakeSmokeTest` and `RealSmokeTest` covering resolver registration and base architecture.

### 🔧 Changed
- **Interface Compliance**
    - `BaseRepository` now fully implements `RepositoryInterface` with fluent `setAdapter()`.

---

[1.0.2]: https://github.com/Maatify/data-repository/releases/tag/1.0.2
[1.0.1]: https://github.com/Maatify/data-repository/releases/tag/1.0.1
[1.0.0]: https://github.com/Maatify/data-repository/releases/tag/1.0.0
