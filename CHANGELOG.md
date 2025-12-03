# 📘 Changelog

All notable changes to **maatify/data-repository** will be documented in this file.

The format is based on **[Keep a Changelog](https://keepachangelog.com/en/1.0.0/)**  
and the project adheres to **[Semantic Versioning](https://semver.org/spec/v2.0.0.html)**.

---

## [Unreleased]

## [1.0.9] — 2025-12-02 (Phase 24 Update)

### 🚀 Added
- **OrderParser Extraction (Phase 24)**
    - Extracted order parsing logic into `Maatify\DataRepository\Generic\Support\OrderParser`.
    - Introduced `Maatify\DataRepository\Generic\Support\OrderField` value object.
    - Updated `OrderUtils` to delegate to `OrderParser` for normalization.
    - Added unit tests for `OrderParser` verifying sanitization and direction validation.

## [1.0.9] — 2025-12-02 (Phase 23 Update)

### 🚀 Added
- **Filter Builders (Phase 23)**
    - Added `Maatify\DataRepository\Generic\Support\MySQLFilterBuilder` for dedicated SQL WHERE clause generation.
    - Added `Maatify\DataRepository\Generic\Support\MongoFilterBuilder` for MongoDB query structure generation.
    - Refactored `FilterUtils` to act as a facade, delegating to the new builders while maintaining public API compatibility.
    - Added extensive unit tests for both builders covering standard equality, complex operators (`IN`, `LIKE`, `BETWEEN`), and edge cases.

- **FilterParser Extraction (Phase 22)**
    - Extracted filter parsing logic into `Maatify\DataRepository\Generic\Support\FilterParser`.
    - Introduced `Maatify\DataRepository\Generic\Support\FieldFilter` value object to represent parsed filter conditions.
    - Updated `FilterUtils` to use `FilterParser` for normalization, separating parsing concerns from query generation.
    - Added comprehensive unit tests for `FilterParser` covering equality, operators, nulls, and error handling.

---

## [1.0.8] — 2025-12-02 (Phase 21 Update)

### 🚀 Added
- **Architecture Decoupling (Phase 21)**
    - Refactored `BaseRepository` to allow raw `Psr\Log\LoggerInterface` injection without forced `RepositoryLogger` wrapping.
    - Updated `BaseRepository::__construct` to default to `NullLogger` if no logger is provided.
    - Added `tests/Architecture/LoggerInjectionTest.php` verifying injection behavior (Null, Raw, Wrapped).
    - Note: This is a behavioral change; logs will no longer automatically include the `source` context unless manually wrapped in `RepositoryLogger`.

---

## [1.0.7] — 2025-12-01 (Phase 20 Update)

### 🚀 Added
- **SQL & Filter Improvements (Phase 20)**
    - **Semantic Placeholders:** Refactored `FilterUtils::buildSqlWhere` to use column names as placeholders (e.g., `:status`, `:age_GT`) instead of generic indices (`:p0`). This improves debuggability of generated SQL.
    - **Safe BigInt Handling:** Refactored `MysqlOps::lastInsertId` to robustly handle 64-bit integers. It now safely preserves IDs as strings if they exceed `int` precision, while strictly casting standard IDs to `int`.
    - Added verification tests in `tests/Generic/SQL/`.
    - Updated `AdvancedFilterFakeTest` and `FilterUtilsCoverageTest` to reflect new semantic placeholder format.
    - Included usage examples in `examples/phase20/`.

---

## [1.0.6] — 2025-12-01 (Phase 19 Update)

### 🚀 Added
- **Mongo Robustness (Phase 19)**
    - Added `setCollectionName(string $name)` to `GenericMongoRepository` for dynamic collection switching.
    - Updated `getCollectionObj()` to prioritize explicit collection names while maintaining backward compatibility with `tableName`.
    - Added `tests/Generic/NoSQL/MongoRobustnessTest.php`.

- **Redis Filtering (Phase 19)**
    - Implemented in-memory `findBy`, `findOneBy`, and `paginateBy` for `GenericRedisRepository`.
    - Added support for basic equality and `IN` list filtering on Redis JSON values.
    - Verified functionality via `tests/Generic/NoSQL/RedisFilteringTest.php`.
    - Note: Redis filtering performs a full scan of keys matching the prefix; suitable for small datasets only.

---

## [1.0.4] — 2025-11-27 (Phase 17 Update)

### 🚀 Added
- **Paginated Hydrated Results (Phase 17)**
    - Introduced `HydratedPaginationCollection` DTO for type-safe handling of paginated, hydrated object sets.
    - Updated `RepositoryHydrationTrait` with `paginateObjects()` and `paginateObjectsBy()` methods.
    - Enabled `GenericRepositories` to return fully hydrated object collections while retaining pagination metadata.
    - Verified behavior via `HydratedPaginationTest` ensuring correct hydration of paginated subsets.

---

## [1.0.5] — 2025-11-27 (Phase 18 Update)

### 🚀 Added
- **Integration Matrix (Phase 18)**
    - Implemented `tests/Integration/IntegrationValidatorTest.php` as a foundation for rigorous adapter parity testing.
    - Implemented `tests/Integration/FakeVsRealMatrixTest.php` to execute the same test logic against "Fake" and "Real" repository implementations.
    - Added comprehensive Matrix-style tests ensuring `insert`, `find`, `findAll`, `update`, and `delete` behave consistently across adapters.
    - Prepared structure for verifying integration with external `data-fakes` and `data-integrations` libraries.

---

## [1.0.4] — 2025-11-27 (Phase 16 Update)

### 🚀 Added
- **Pagination Optimization (Phase 16)**
    - Optimized `GenericRedisRepository` pagination: Now fetches all keys (lighter) first, slices them, and only fetches values for the current page (lazy loading).
    - Verified `GenericMySQLRepository` utilizes standard SQL `LIMIT` and `OFFSET` for efficient database-side paging.
    - Verified `GenericMongoRepository` utilizes native MongoDB `limit` and `skip` cursor options.
    - Added verification tests in `tests/Pagination/Optimization/`.

---

## [1.0.4] — 2025-11-27 (Phase 15 Update)

### 🚀 Added
- **Pagination Core (Phase 15)**
    - Implemented standardized `paginate(page, perPage)` and `paginateBy(filters, page, perPage)` methods in Generic Repositories.
    - Added `PaginationResultDTO` wrapping data array and metadata.
    - Restored/Verified `PaginationEntry` and `PaginationContext` for internal use.
    - Integrated `maatify/common` `PaginationDTO` for unified pagination metadata across the system.
    - Added `paginate()` support to `GenericMySQLRepository`, `GenericMongoRepository`, and `GenericRedisRepository`.
    - Note: `GenericRedisRepository::paginateBy` throws exception as Redis lacks secondary index support for filtering.

---

## [1.0.3] — 2025-11-27 (Phase 14 Update)

### 🚀 Added
- **DTO Mapping & Hydration Integration (Phase 14)**
    - Added `MappingProfile` for defining field-to-property mapping rules, transformers, and default values.
    - Added `TransformerInterface` and concrete implementations: `JsonTransformer`, `DateTimeTransformer`.
    - Integrated `MappingProfile` into `BaseHydrator::onMap()` via `HydrationContext`.
    - Added `setHydrator()` and `getHydrator()` to `BaseRepository`.
    - Added `RepositoryHydrationTrait` providing `findObject()` and `findObjectsBy()` methods for hydrated results in Generic Repositories.
    - Updated `GenericMySQLRepository`, `GenericMongoRepository`, and `GenericRedisRepository` to support hydration.
    - Verified functionality with `DtoHydrationIntegrationTest` and `GenericRepositoryHydrationTest`.

---

## [1.0.3] — 2025-11-27 (Phase 13 Update)

### 🚀 Added
- **AutoCasting System (Phase 13)**
    - Added `AutoCaster` utility for strict type conversion (int, float, bool, string, datetime, json).
    - Integrated `AutoCaster` into `BaseHydrator::onCast()` pipeline stage.
    - Added `getCastingDefinitions()` hook in `BaseHydrator` to allow declarative casting rules in concrete repositories.
    - Verified strict typing behavior and JSON array normalization via unit tests.

---

## [1.0.2] — 2025-11-27 (Phase 12 Update)

### 🚀 Added
- **Base Hydrator (Phase 12)**
    - Implemented `BaseHydrator` abstract class executing the hydration pipeline.
    - Added overridable lifecycle hooks: `onPrepare`, `onCast`, `onMap`, `onValidate`, `onComplete`.
    - Added `hydrate()` and `hydrateAll()` implementation.
    - Verified pipeline execution order and context stage customization via unit tests.

---

## [1.0.2] — 2025-11-27 (Phase 11 Update)

### 🚀 Added
- **Hydrator Interface (Phase 11)**
    - Defined `HydratorInterface` with `hydrate()` and `hydrateAll()` methods.
    - Introduced `HydrationContext` for managing hydration stages and metadata.
    - Added unit tests for interface contracts and context behavior.

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
    - Verified invalid types handling.
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

[1.0.9]: https://github.com/Maatify/data-repository/releases/tag/1.0.9
[1.0.8]: https://github.com/Maatify/data-repository/releases/tag/1.0.8
[1.0.7]: https://github.com/Maatify/data-repository/releases/tag/1.0.7
[1.0.6]: https://github.com/Maatify/data-repository/releases/tag/1.0.6
[1.0.4]: https://github.com/Maatify/data-repository/releases/tag/1.0.4
[1.0.3]: https://github.com/Maatify/data-repository/releases/tag/1.0.3
[1.0.2]: https://github.com/Maatify/data-repository/releases/tag/1.0.2
[1.0.1]: https://github.com/Maatify/data-repository/releases/tag/1.0.1
[1.0.0]: https://github.com/Maatify/data-repository/releases/tag/1.0.0
