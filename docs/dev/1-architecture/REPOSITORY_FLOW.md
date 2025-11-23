![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🔄 Repository Flow Guide  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Describe the *exact data flow* through the repository layer  
from the moment the developer calls a repository method →  
until the final DTO/array result is returned.

This file ensures:

- No hidden behavior  
- No implicit assumptions  
- Unified design for all drivers  
- Identical flow for Fake + Real  
- Executor Engine does NOT invent logic  
- The system behaves consistently across MySQL, Redis, MongoDB  

This is the **official execution path** for every repository.

---

# 📚 Table of Contents

1. [High-Level Overview](#high-level-overview)  
2. [Initialization Flow](#initialization-flow)  
3. [Execution Flow (Full Pipeline)](#execution-flow-full-pipeline)  
4. [Driver Resolution Flow](#driver-resolution-flow)  
5. [Normalization Flow](#normalization-flow)  
6. [Validation Flow](#validation-flow)  
7. [Filtering Flow](#filtering-flow)  
8. [Hydration Flow](#hydration-flow)  
9. [Return Result Flow](#return-result-flow)  
10. [Fake vs Real Equivalence](#fake-vs-real-equivalence)  
11. [Error Flow & Exceptions](#error-flow--exceptions)  
12. [Forbidden Flows](#forbidden-flows)  
13. [Complete Flow Diagram](#complete-flow-diagram)

---

# 🟩 High-Level Overview

Every repository call follows this exact sequence:

```

Repository Method
↓
Resolver → Adapter → Driver
↓
Driver Normalization
↓
Query Execution (MySQL/Redis/Mongo)
↓
Filtering + Validation
↓
Hydration (optional)
↓
Return: array | DTO | scalar

````

No repository may deviate from this structure.

---

# 🧱 Initialization Flow

Before ANY repository method runs, the following is already initialized:

1. **Repository instance created**
2. **Adapter injected** (via constructor or setAdapter)
3. **Optional logger injected**
4. **Optional hydrator injected**

If the adapter is NOT injected →  
this MUST trigger `RepositoryException`.

Mandatory rule:

> Repository MUST NOT instantiate adapters itself.  
> AdapterInjection ONLY.

---

# 🔄 Execution Flow (Full Pipeline)

When a method like:

```php
$repo->find(10);
````

is called, the following mandatory pipeline executes:

### 1) Repository receives request

* Validate input
* Prepare filter/criteria

### 2) Acquire adapter

```php
$adapter = $this->adapter();
```

### 3) Acquire driver

```php
$driver = $adapter->getDriver();
```

### 4) Normalize driver

Using explicit `instanceof` matrix
→ determine pipeline (PDO / DBAL / Redis / Mongo / Fake)

### 5) Execute query

Method delegates to normalized driver path
→ run DB query / Redis command / Mongo operation

### 6) Validate output

* Ensure array shape
* Prevent mixed/null leaks
* Normalize types

### 7) Filter (if any)

Apply common filters from maatify/common
→ strip disallowed fields
→ cast values
→ apply search conditions

### 8) Hydrate (optional)

Convert arrays → DTO objects
(using HydratorInterface)

### 9) Return result

Return type may be:

* array<string,mixed>
* array<int,array<string,mixed>>
* DTO
* list<DTO>
* scalar (int/string)
* boolean

No other types allowed.

---

# 🧭 Driver Resolution Flow

Driver resolution:

```
$adapter → getDriver()
```

AdapterInterface comes from:

* data-adapters (REAL)
* data-fakes (FAKE)
* common (interface only)

Drivers may be:

### MySQL

* PDO
* DBAL Connection
* FakeStorageLayer

### Redis

* Redis (phpredis)
* Predis\Client
* FakeRedisAdapter

### Mongo

* MongoDB\Database
* MongoDB\Collection
* FakeStorageLayer

Repositories **never** interact directly with adapters.
They ONLY interact with drivers after normalization.

---

# 🟦 Normalization Flow

This ensures the repository chooses the correct pipeline:

```
if MySQL:
   PDO → pdo pipeline
   DBAL → dbal pipeline
   Fake → fake mysql pipeline

if Redis:
   Redis → redis pipeline
   Predis → predis pipeline
   Fake → fake redis pipeline

if Mongo:
   Database → select collection
   Collection → direct operations
   Fake → fake mongo pipeline
```

Normalization MUST always precede any query execution.

---

# 🧹 Validation Flow

Validation (from maatify/common) MUST run **before hydration**.

Validation responsibilities:

* required fields
* type checks
* allowed keys
* remove unsafe keys
* ensure non-null when required

Validation layer MUST NOT throw exceptions from drivers.
Only RepositoryException.

---

# 🔍 Filtering Flow

Filtering is applied after fetching but before hydration.

Filtering features:

* allow-list / block-list fields
* pagination
* sorting
* pattern matching
* numeric casting
* cleanup of internal fields

Filtering guarantees the output structure is consistent.

---

# 💧 Hydration Flow

If repository supports hydration:

```
$hydrator->hydrate(UserDTO::class, $data);
```

Hydration MUST:

* accept validated arrays
* be deterministic
* not depend on driver type
* not mutate state
* not perform logic outside mapping

Hydration is optional per method.

---

# 🏁 Return Result Flow

Repositories can return:

### 1) array<string,mixed>

### 2) array<int,array<string,mixed>>

### 3) DTO instance

### 4) list<DTO>

### 5) scalar (int|string|bool)

Repositories MUST NOT return:

* mixed
* untyped arrays
* objects not produced by hydrator
* drivers
* adapters

---

# 🧪 Fake vs Real Equivalence

The flow MUST behave identically with:

### Real drivers:

✓ PDO
✓ DBAL
✓ Redis
✓ Predis
✓ MongoDB

### Fake drivers:

✓ FakeMySQLAdapter
✓ FakeMySQLDbalAdapter
✓ FakeRedisAdapter
✓ FakeMongoAdapter
✓ FakeResolver
✓ FakeEnvironment

FakeStorageLayer rows MUST be shaped like real rows.
Normalization MUST treat fakes as 100% real from the repository’s point of view.

---

# ❗ Error Flow & Exceptions

Errors must follow this exact rule:

### If driver failure → convert to RepositoryException

### If invalid driver → throw RepositoryException

### If invalid input → RepositoryException

### If adapter missing → RepositoryException

### If hydrator fails → RepositoryException

NEVER throw raw driver exceptions.

---

# 🚫 Forbidden Flows

Repositories MUST NOT:

* call `new PDO()` or any driver constructor
* instantiate Fake drivers
* touch FakeStorageLayer directly
* depend on data-fakes inside production code
* modify environment state
* use global state
* bypass normalization
* return arbitrary objects
* catch driver-level exceptions without rethrowing RepositoryException
* store adapters statically

---

# 🗺 Complete Flow Diagram

```
RepoMethod()
      |
      v
[Validate Input]
      |
      v
[Get Adapter]
      |
      v
AdapterInterface::getDriver()
      |
      v
[Driver Normalization]
      |
      +→ if PDO → mysql-pdo pipeline
      |
      +→ if DBAL → mysql-dbal pipeline
      |
      +→ if FakeStorageLayer → fake-mysql pipeline
      |
      +→ if Redis → redis pipeline
      |
      +→ if Predis → predis pipeline
      |
      +→ if MongoDB → mongo pipeline
      |
      +→ else → throw RepositoryException
      |
      v
[Execute Query/Command]
      |
      v
[Output Validation]
      |
      v
[Filtering]
      |
      v
[Hydration (optional)]
      |
      v
[Return DTO / array / scalar]
```

---

# 🎉 Summary

The repository flow ensures:

* rigid architecture
* predictable behavior
* driver-agnostic execution
* deterministic tests
* zero hidden logic
* perfect compatibility with `data-fakes` and `data-adapters`

This document MUST NOT change outside roadmap updates
and is now part of the authoritative project documentation.
