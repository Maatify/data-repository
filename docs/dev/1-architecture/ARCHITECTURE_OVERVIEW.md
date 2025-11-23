![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🏛 Architecture Overview  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative Core Document  
**Purpose:** Define the *complete architectural structure* of the repository layer,  
its dependencies, data flow, boundaries, responsibilities, and interaction  
with all related Maatify libraries.

This document is the **top-level architecture reference**  
and MUST guide all future phases, designs, and roadmap decisions.

---

# 📚 Table of Contents

1. [High-Level Architecture](#high-level-architecture)  
2. [Core Principles](#core-principles)  
3. [Layered Structure](#layered-structure)  
4. [Dependency Boundaries](#dependency-boundaries)  
5. [Relationships Between Libraries](#relationships-between-libraries)  
6. [Adapters Layer](#adapters-layer)  
7. [Repository Layer](#repository-layer)  
8. [Fake Testing Layer](#fake-testing-layer)  
9. [Resolvers](#resolvers)  
10. [Common Components](#common-components)  
11. [Bootstrap Integration](#bootstrap-integration)  
12. [Logging Integration](#logging-integration)  
13. [Control Flow](#control-flow)  
14. [Forbidden Architecture Rules](#forbidden-architecture-rules)  
15. [Future Extensibility](#future-extensibility)

---

# 🟦 High-Level Architecture

The data-repository ecosystem is built on a **strict layered architecture**:

```

Application / Services
↓
Repositories Layer
↓
Resolver Layer
↓
Adapters Layer (REAL)
↓
Drivers (PDO, DBAL, Redis, Predis, MongoDB)

Tests: Fake Drivers (data-fakes)

```

Each layer has **one specific responsibility**  
and MUST NOT exceed or leak into another.

---

# 🧩 Core Principles

### ✔ 1. Single Responsibility  
Each layer does exactly one job — nothing more.

### ✔ 2. Strict Abstraction  
Repositories use:
- `RepositoryInterface`
- `AdapterInterface`

Repositories MUST NOT know anything about:
- PDO
- MongoDB\Client
- Redis
- Predis\Client
- FakeStorageLayer  
→ before normalization

### ✔ 3. Determinism  
Identical behavior with:
- REAL drivers  
- FAKE drivers  

### ✔ 4. Testability  
Fakes simulate *only* driver behavior  
Real adapters validate real-world correctness.

### ✔ 5. Clear Boundaries  
No cross-dependencies, no shortcuts, no implicit behaviors.

---

# 🏗 Layered Structure

```

Domain Services (Application Layer)
↓
Repositories (data-repository)
↓
Resolvers (data-adapters resolver)
↓
Adapters (data-adapters)
↓
Drivers (native)

```

In tests:

```

Repositories
↓
FakeResolver (data-fakes)
↓
FakeAdapters
↓
FakeDrivers/FakeStorageLayer

```

---

# 🛑 Dependency Boundaries

The repository layer depends on:

| Dependency            | Purpose                                                      |
|-----------------------|--------------------------------------------------------------|
| maatify/common        | AdapterInterface, RepositoryInterface, pagination, validator |
| maatify/data-adapters | REAL adapters (PDO, DBAL, Redis, Predis, MongoDB)            |
| maatify/data-fakes    | FAKE adapters + FakeStorageLayer for tests                   |
| maatify/bootstrap     | EnvHelper, PathHelper, IntegrationValidator                  |
| maatify/psr-logger    | PSR-3 logger                                                 |

### ❗ Repositories MUST NOT depend on:
- Fake classes  
- drivers directly  
- native constructors  
- global state  

---

# 🔌 Adapters Layer

Adapters come from:

### **REAL (production)**  
`maatify/data-adapters`

These return real drivers:

- `PDO`
- `Doctrine\DBAL\Connection`
- `MongoDB\Database`
- `MongoDB\Collection`
- `Redis`
- `Predis\Client`

### **FAKE (testing)**  
`maatify/data-fakes`

These return:

- FakeStorageLayer (MySQL/DBAL/Mongo)
- FakeRedisAdapter
- FakeResolver
- FakeEnvironment

---

# 🗄 Repository Layer

Repositories are responsible ONLY for:

- Normalizing drivers  
- Executing queries (via driver methods)  
- Validating input/output  
- Applying filters  
- Hydrating DTOs  
- Throwing `RepositoryException` on failure  
- Logging through PSR-3  

Repositories NEVER:

- instantiate drivers  
- instantiate adapters  
- interact with Fake classes  
- perform IO or filesystem  
- access raw connections  
- bypass the resolver  

---

# 🧭 Resolver Layer

### What it does:
- Converts `route` → correct adapter instance
- Returns adapter implementing `AdapterInterface`
- Adapter returns REAL/FAKE driver

### Where it lives:
`data-adapters` (real)  
`data-fakes` (testing)

### Forbidden:
Repositories MUST NOT resolve adapters manually.

---

# 🧰 Common Components

Coming from `maatify/common`:

| Component           | Purpose                        |
|---------------------|--------------------------------|
| AdapterInterface    | unifies connection contracts   |
| RepositoryInterface | unifies repository design      |
| PaginationDTO       | pagination support             |
| Validator           | validate inserts and updates   |
| Filter              | filter arrays before hydration |

Repositories MUST use these components consistently.

---

# 🧱 Bootstrap Integration

From `maatify/bootstrap`:

- `EnvHelper` → environment variables  
- `PathHelper` → resolve paths  
- `IntegrationValidator` → ensures adapters+logger are loaded correctly  

Repositories MUST integrate with bootstrap constraints.

---

# 📝 Logging Integration

Using `maatify/psr-logger`:

- All failures MUST be logged  
- All normalization errors MUST be logged  
- All driver issues MUST be logged  
- Repositories MUST accept `LoggerInterface|null`

Logging MUST NOT perform side effects.

---

# 🔄 Control Flow

The full pipeline:

```

Repository Method Call
↓
Validate Input
↓
Resolve Adapter (Resolver)
↓
AdapterInterface::connect()
↓
Driver = AdapterInterface::getDriver()
↓
Normalize Driver (PDO / DBAL / Redis / Predis / Mongo / Fake)
↓
Execute Operation
↓
Output Validation
↓
Filtering
↓
Hydration
↓
Return result

```

---

# 🚫 Forbidden Architecture Rules

### ❌ Repositories MUST NOT:
- instantiate PDO, Redis, MongoDB, Predis  
- depend on data-fakes inside production code  
- bypass driver normalization  
- use global/static state  
- swallow driver exceptions silently  
- store adapters statically  
- assume driver type (must ALWAYS use `instanceof`)  

### ❌ Adapters MUST NOT:
- return invalid drivers  
- break AdapterInterface contract  
- hide exceptions without rethrowing  

### ❌ Fake Layer MUST NOT:
- be imported in source code  
- leak fake behavior into production  

---

# 🚀 Future Extensibility

Architecture is already ready for:

- Query Builder (Phase 7)  
- Unit of Work (Phase 8)  
- Repository Observers  
- Cache Decorators  
- Multi-database routing  
- Entity Tracking  

All without modifying existing layers  
because boundaries are properly defined.

---

# 🧩 Summary

This file defines:

- The entire architecture  
- Exact roles of each layer  
- Allowed vs forbidden interactions  
- Driver boundaries  
- Fake testing boundaries  
- Data flow  
- Resolver behavior  
- Adapter behavior  
- Repository behavior  
- Logging, validation, filtering, hydration  

This is the **master architectural document**  
and MUST NOT change outside roadmap updates.
