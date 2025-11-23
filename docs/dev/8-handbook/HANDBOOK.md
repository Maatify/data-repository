![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 📘 **HANDBOOK.md**

### **Maatify Data Repository – Developer Handbook**

**Version:** 1.0.0
**Status:** Authoritative
**Audience:** Maintainers, Contributors, Integrators

---

# 1. Introduction

`maatify/data-repository` is the unified data-access abstraction for the entire Maatify ecosystem.
It provides a strict, deterministic, strongly-typed repository layer above:

* **maatify/common** — Contracts, RepositoryInterface, validation, pagination
* **maatify/bootstrap** — EnvHelper, PathHelper, IntegrationValidator
* **maatify/psr-logger** — PSR-3 compatible logging
* **maatify/data-adapters** — Real PDO/DBAL/Redis/Predis/Mongo adapters
* **maatify/data-fakes** — Fake drivers for deterministic testing

This library defines how repositories must behave, how they normalize drivers, how they validate schema, and how data flows across the system.

This handbook is the *single source of truth* for all contributors.

---

# 2. Repository Philosophy

The repository layer follows these principles:

### ✔ Driver-agnostic

Repositories operate purely through **AdapterInterface**.
They never instantiate PDO, Redis, MongoDB, DBAL, or Predis drivers.

### ✔ Strict typing

No mixed types.
No dynamic properties.
All arrays use generics (array<string,mixed>).

### ✔ Deterministic behavior

Fake tests must behave identically to real tests.

### ✔ Explicit schema

Repositories define their schema explicitly; never inferred from database metadata.

### ✔ Strong validation

Filtering, sorting, DTO hydration, insert/update rules — all validated at runtime.

### ✔ Isolated failure domain

Repository catches all driver-level exceptions and converts them into library-defined exceptions.

---

# 3. Architecture Overview

```
Application Layer
        │
        ▼
 RepositoryInterface (maatify/common)
        │
        ▼
 BaseRepository (this library)
        │
        ▼
 AdapterInterface (maatify/common)
        │
        ▼
 ┌────────────────────────────────────┐
 │    Real Drivers (data-adapters)    │
 │    Fake Drivers (data-fakes)       │
 └────────────────────────────────────┘
```

The repository **never** creates drivers.
It *only consumes* adapters through AdapterInterface.

---

# 4. Driver Matrix

## MySQL / DBAL

| Source        | Driver                   | Repository          |
|---------------|--------------------------|---------------------|
| data-adapters | PDO                      | BaseMySQLRepository |
| data-adapters | Doctrine\DBAL\Connection | BaseMySQLRepository |
| data-fakes    | FakeStorageLayer (MySQL) | BaseMySQLRepository |
| data-fakes    | FakeStorageLayer (DBAL)  | BaseMySQLRepository |

## Redis / Predis

| Source        | Driver           | Repository          |
|---------------|------------------|---------------------|
| data-adapters | Redis (phpredis) | BaseRedisRepository |
| data-adapters | Predis\Client    | BaseRedisRepository |
| data-fakes    | FakeRedisAdapter | BaseRedisRepository |

## MongoDB

| Source        | Driver                   | Repository          |
|---------------|--------------------------|---------------------|
| data-adapters | MongoDB\Database         | BaseMongoRepository |
| data-adapters | MongoDB\Collection       | BaseMongoRepository |
| data-fakes    | FakeStorageLayer (Mongo) | BaseMongoRepository |

---

# 5. Driver Normalization

Every repository must normalize drivers using strict `instanceof` checks:

```php
$driver = $adapter->getDriver();

if ($driver instanceof PDO) { … }
elseif ($driver instanceof Doctrine\DBAL\Connection) { … }
elseif ($driver instanceof Redis) { … }
elseif ($driver instanceof Predis\Client) { … }
elseif ($driver instanceof MongoDB\Database) { … }
elseif ($driver instanceof MongoDB\Collection) { … }
elseif ($driver instanceof FakeStorageLayer) { … }
else {
    throw RepositoryDriverException('Unsupported driver type');
}
```

**No assumptions. No shortcuts. No mixed behavior.**

---

# 6. Schema System

Every repository must implement:

```php
protected function schema(): array;
```

Schema defines:

* All fields
* Types (int/string/bool/float/datetime/array/json/enum)
* Required vs optional
* Insertable fields
* Updatable fields
* Filterable fields
* Sortable fields
* DTO mapping
* Primary key

Schema is:

* Static
* Code-defined
* Version-controlled
* Independent of the database
* Mandatory for all validation

Dynamic discovery is forbidden.

---

# 7. Repository Structure

```
src/
  Base/
    BaseRepository.php
    BaseMySQLRepository.php
    BaseMongoRepository.php
    BaseRedisRepository.php

  Contracts/
  Exceptions/
  Traits/
  Hydration/
  Resolver/
  Helpers/
```

Each repository:

* Extends BaseRepository
* Implements RepositoryInterface
* Defines a schema
* Implements driver pipelines
* Defines a DTO

---

# 8. Hydration Layer

Hydration converts raw arrays → strongly typed DTO objects.

Rules:

* snake_case → camelCase
* datetime strings → DateTimeImmutable
* array<string,mixed> → typed arrays
* DTO properties must match schema types
* No arbitrary casting

Components:

* `HydratorInterface`
* `SimpleHydrator`

---

# 9. Exceptions Layer

Unified exception types:

| Exception Class               | Meaning                          |
|-------------------------------|----------------------------------|
| RepositoryException           | Base class                       |
| RepositoryConnectionException | Adapter/driver failed to connect |
| RepositoryTimeoutException    | Operation exceeded time limit    |
| RepositoryDriverException     | Invalid driver/protocol mismatch |
| RepositoryDataException       | Invalid data or type mismatch    |

Rules:

* Repository must NEVER leak PDOException, RedisException, MongoDB driver exceptions.
* All driver-level errors must be normalized.

---

# 10. Resiliency Rules

The repository must enforce:

### Connection Handling

* Validate adapter connection state
* Attempt reconnection when required
* Perform driver health checks

### Retry Logic

* Disabled by default
* Enabled only for idempotent operations
* Max 3 retries
* Exponential backoff

### Timeout Enforcement

* Default timeout: 1500ms
* Latency simulation must mimic real driver timeouts

### Fake/Real Symmetry

Fake tests must replicate real-world failures using:

* ErrorSimulator
* LatencySimulator
* Broken connection simulation
* Data corruption scenarios

---

# 11. Filtering & Sorting

Filtering:

* Allowed only on `filterable` fields
* Uses maatify/common filters
* Rejects unknown fields
* Rejects type mismatches
* Rejects unsafe operators

Sorting:

* Allowed only on `sortable` fields
* Direction must be `ASC` or `DESC`

---

# 12. Pagination

All repositories must support:

* `PaginationDTO`
* Total count
* Slice of items
* Structured result format:

```
[
  'items' => [...],
  'pagination' => PaginationDTO
]
```

---

# 13. Validation

Insert/update validation includes:

* Required fields
* Type checking
* Default values
* JSON/datetime casting
* Forbidden keys check

---

# 14. Testing Philosophy

### Fake Tests (Primary)

Fake tests must validate:

* CRUD
* Filters & Sort
* Schema validation
* Insert/update rules
* Resiliency (latency, timeouts, failures)
* Correct DTO hydration
* Fake behavior equals real behavior

### Real Tests (Secondary)

Real drivers must match the fake environment exactly.

Drivers tested:

* PDO
* DBAL
* Redis
* Predis
* MongoDB

---

# 15. Test Matrix

| Test Type       | Fake | Real |
|-----------------|------|------|
| CRUD            | ✔    | ✔    |
| Exceptions      | ✔    | ✔    |
| Timeout         | ✔    | ✔    |
| Retry           | ✔    | ✔    |
| Filtering       | ✔    | ✔    |
| Sorting         | ✔    | ✔    |
| Hydration       | ✔    | ✔    |
| Inserts/Updates | ✔    | ✔    |

Fake tests guarantee determinism.
Real tests guarantee correctness.

---

# 16. CI Rules

GitHub workflow must include:

* PHPUnit
* PHPStan level-max
* PHP-CS-Fixer
* Real service containers (Redis, MySQL, MongoDB)
* Fake testing suite
* Adapter health checks
* Optional Telegram notifications

Nothing may be merged unless both fake + real tests pass.

---

# 17. RepositoryResolver

Responsibilities:

* Resolve correct adapter
* Inject adapter into repository
* Validate adapter implementation
* Validate driver type
* Guarantee driver readiness
* Provide unified entry point for all repositories

Flow:

```
Client → RepositoryResolver → AdapterInterface → Repository
```

---

# 18. Full Repository Flow

```
Repository call
    ↓
Validate schema
    ↓
Validate filters/sort/data
    ↓
adapter->connect()
    ↓
Validate driver instance
    ↓
Normalize driver pipeline
    ↓
Execute operation (MySQL/Redis/Mongo)
    ↓
Normalize result
    ↓
DTO hydration
    ↓
Return typed output
```

---

# 19. Developer Workflow

To create a new repository:

1. Create repository class
2. Implement RepositoryInterface
3. Extend BaseRepository
4. Define schema()
5. Create DTO
6. Implement driver pipelines
7. Add fake tests
8. Add real tests
9. Extend RepositoryResolver
10. Update documentation

---

# 20. Examples

Located in `/examples/`:

* MySQL CRUD
* DBAL CRUD
* Redis operations
* Mongo operations
* DTO hydration
* Filtering & sorting
* Pagination
* Schema definition

---

# 21. Summary

This handbook defines:

* architecture
* schema
* drivers
* normalization
* exceptions
* resiliency
* hydration
* filtering
* sorting
* pagination
* logging
* testing
* CI
* resolver
* full data flow

All future features must follow these rules.

This is the authoritative reference for developing
and maintaining **maatify/data-repository**.

---
