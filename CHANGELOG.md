# 📘 Changelog

All notable changes to **maatify/data-repository** will be documented in this file.

The format is based on **[Keep a Changelog](https://keepachangelog.com/en/1.0.0/)**  
and the project adheres to **[Semantic Versioning](https://semver.org/spec/v2.0.0.html)**.

---

## [Unreleased]

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
    - Implemented `LimitOffsetValidator` for unified validation (Limits 1-10000, Offsets 0-10000).
    - Added `GenericMySQLRepository` integration (SQL `LIMIT`/`OFFSET`).
    - Added `GenericMongoRepository` integration (Mongo `limit`/`skip`).
    - Added `GenericRedisRepository` integration (throws `RepositoryException` as it is not supported yet).

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
