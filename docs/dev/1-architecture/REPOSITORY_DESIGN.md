![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 📘 **docs/REPOSITORY_DESIGN.md**

**Project:** maatify/data-repository
**Version:** 1.0.0
**Status:** Architectural Specification
**Purpose:** Document the complete architectural relationships between Repository, Adapter, Resolver, Logger, Validation, and Testing layers.

---

# 🧭 Overview

`maatify/data-repository` provides the **repository abstraction layer**
for all Maatify applications.

Its role is to sit between:

```
Domain Logic
      ↓
Repository Layer (this library)
      ↓
AdapterInterface (common)
      ↓
Real/Fake Drivers (data-adapters / data-fakes)
```

Repositories DO NOT directly talk to PDO, MongoDB, Redis, DBAL, or Predis.
They ONLY communicate through a unified abstraction (`AdapterInterface`)
and normalize drivers through runtime type detection.

---

# 🏛 Layered Architecture

## 📌 Architecture Stack (Top → Bottom)

```
Application Layer
    ↓
Repository Layer (this package)
    ↓
RepositoryResolver (this package)
    ↓
AdapterInterface (maatify/common)
    ↓
Real Adapters (maatify/data-adapters)
Fake Adapters (maatify/data-fakes)
    ↓
Native Drivers (PDO, DBAL, MongoDB, Redis, Predis)
```

Each layer has a strict responsibility and **MUST NOT bypass** the layer beneath it.

---

# 🧱 Components & Responsibilities

## 1) **Repository Layer (this package)**

Contains:

* `BaseRepository`
* `BaseMySQLRepository`
* `BaseMongoRepository`
* `BaseRedisRepository`
* `Hydration Layer`
* `Validation / Filtering`
* `Pagination`

### Responsibilities:

* Interact ONLY with `AdapterInterface`
* Normalize the driver type
* Provide CRUD and helper operations
* Wrap low-level exceptions inside `RepositoryException`
* Use logging (PSR-3) and environment helpers
* Validate data inputs before insert/update

### Forbidden:

* Creating PDO / MongoDB / Redis / DBAL connections
* Using FakeRepository
* Calling `getConnection()` (use getDriver() only)
* Assuming array shapes or key existence
* Using ANY fake class directly inside the repository

---

## 2) **RepositoryResolver (this package)**

Acts as the DI entry point.

Responsibilities:

* Select the correct adapter based on configuration/route
* Inject adapter into repositories
* Validate that AdapterInterface is implemented
* Ensure correct driver → correct repository

Forbidden:

* Returning fake adapters
* Returning raw drivers

---

## 3) **AdapterInterface (maatify/common)**

The universal contract between repository and adapter.

Required methods:

* `connect()`
* `isConnected()`
* `getDriver()` ← **ONLY this may be used**
* `healthCheck()`
* `disconnect()`

Forbidden:

* Calling `getConnection()` from repositories

---

## 4) **Real Adapters (maatify/data-adapters)**

They return REAL drivers:

* PDO
* Doctrine DBAL Connection
* Redis (phpredis)
* Predis\Client
* MongoDB\Database
* MongoDB\Collection

Repositories MUST normalize these drivers using the rules in `DRIVERS.md`.

---

## 5) **Fake Adapters (maatify/data-fakes)**

Used ONLY in:

* Unit tests
* Integration tests
* CI pipelines

Provide:

* FakeMySQLAdapter / FakeMySQLDbalAdapter
* FakeRedisAdapter / FakePredisAdapter
* FakeMongoAdapter
* FakeResolver
* FakeEnvironment (fixtures, snapshots)
* FakeStorageLayer

Repositories MUST **never** include or depend on fake adapters.

---

# 🧩 Relationships Diagram

```
                     +------------------+
                     |  Your Application |
                     +------------------+
                               |
                               v
                     +------------------+
                     |   Repository     |
                     |  (Base + Generic)|
                     +------------------+
                               |
                     RepositoryResolver
                               |
                               v
                     +------------------+
                     | AdapterInterface |
                     | (maatify/common) |
                     +------------------+
                           |         |
             Real Adapter (data-adapters)   Fake Adapter (data-fakes)
                           |         |
                           v         v
             +--------------+     +--------------+
             | Native Driver|     | FakeStorage  |
             | (PDO, DBAL,  |     | / Snapshots  |
             |  Mongo, etc) |     | / Fixtures   |
             +--------------+     +--------------+
```

---

# 🔍 Repository Class Responsibilities (Detailed)

## **BaseRepository**

* Defines common utilities (`adapter()`, `driver()`)
* Wraps driver extraction logic
* Handles PSR-3 logger
* Provides exception normalization
* Shared helper methods

## **BaseMySQLRepository**

* Normalizes: PDO, DBAL, FakeStorageLayer
* CRUD for SQL tables
* Pagination
* Filtering
* Exception mapping (DBAL → RepositoryException)

## **BaseMongoRepository**

* Normalizes: Database, Collection, FakeStorageLayer
* CRUD for Mongo documents
* Ensures collection selection

## **BaseRedisRepository**

* Normalizes: Redis, Predis, FakeRedisAdapter
* Implements key-value operations
* Hash operations
* Counters

---

# 🧪 Testing Strategy (Complete)

## **Fake Testing (data-fakes)**

Used for deterministic behavior:

* Fake MySQL / Fake DBAL
* Fake Redis / Fake Predis
* Fake Mongo
* Fake Resolver
* Fake Environment (fixtures + snapshots)

Goal: Test **repository logic** only.

## **Real Testing (data-adapters)**

Used to ensure compatibility with real drivers:

* PDOAdapter
* DBALAdapter
* RedisAdapter
* PredisAdapter
* MongoAdapter

Goal: Validate “real-world behavior”.

---

# 🔥 Forbidden Behaviors (Critical)

The repository layer MUST NOT:

* Instantiate real drivers
* Use `FakeRepository`
* Use classes from data-fakes (except in tests)
* Rely on array shapes returned by drivers
* Use `getConnection()` instead of `getDriver()`
* Bypass AdapterInterface
* Assume specific behavior for PDO / DBAL / Redis / Mongo

---

# 📘 Integration with Other Maatify Libraries

| Library               | Used For                                    |
|-----------------------|---------------------------------------------|
| maatify/common        | AdapterInterface, validation, pagination    |
| maatify/bootstrap     | EnvHelper, PathHelper, IntegrationValidator |
| maatify/data-adapters | Real adapters                               |
| maatify/data-fakes    | Fake adapters, snapshots, fixtures          |
| maatify/psr-logger    | PSR-3 logging                               |

---

# ✅ Final Notes

* Repositories are the **business-facing layer** of data access.
* Normalization rules MUST match `docs/DRIVERS.md`.
* FakeRepository is explicitly forbidden.
* All repository logic MUST be deterministic and type-safe.
* Driver assumptions MUST be avoided at all costs.

This file + `docs/DRIVERS.md` form the **full architectural documentation**
required before starting Phase 2.

