![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 📜 Repository Contracts (Authoritative Specification)  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative – MUST NOT Change Without Roadmap Update  
**Purpose:** Define the responsibilities, boundaries, and architectural rules  
for all repository classes in this package.

This document is the official contract for how repositories behave,  
interact with adapters, normalize drivers, validate data, apply filters,  
hydrate DTOs, and return consistent results.

---

# 📚 Table of Contents

1. [Overview](#overview)  
2. [RepositoryInterface Contract](#repositoryinterface-contract)  
3. [BaseRepository Responsibilities](#baserepository-responsibilities)  
4. [BaseMySQLRepository Contract](#basemysqlrepository-contract)  
5. [BaseMongoRepository Contract](#basemongorepository-contract)  
6. [BaseRedisRepository Contract](#baseredisrepository-contract)  
7. [Normalization Requirements](#normalization-requirements)  
8. [Validation Rules](#validation-rules)  
9. [Filtering Rules](#filtering-rules)  
10. [Hydration Rules](#hydration-rules)  
11. [Adapter Dependency Rules](#adapter-dependency-rules)  
12. [Return-Type Contract](#return-type-contract)  
13. [Forbidden Behaviors](#forbidden-behaviors)  
14. [Extendable vs Non-Extendable Methods](#extendable-vs-non-extendable-methods)  
15. [Repository Lifecycle](#repository-lifecycle)

---

# 🟦 Overview

The repository layer provides a strict, unified data-access abstraction  
over multiple storage drivers:

- MySQL (PDO + DBAL)  
- Redis (phpredis + Predis)  
- MongoDB (Database + Collection)  
- Fake drivers (for full deterministic testing)  

Repositories MUST operate identically whether drivers are REAL or FAKE.

Repositories MUST NOT:

- instantiate drivers  
- access Fake classes directly  
- bypass adapter  
- bypass resolver  
- perform I/O operations  
- interact with global state  

---

# 🟩 RepositoryInterface Contract

Every repository MUST implement the following:

```

find(id)
findBy(filters)
findAll()
insert(data)
update(id, data)
delete(id)
setAdapter(AdapterInterface)

```

Repositories MUST NOT reduce or break this contract.

They *may* add additional domain-specific methods.

---

# 🟧 BaseRepository Responsibilities

The BaseRepository superclass MUST:

### ✔ Handle:

- adapter injection  
- driver extraction  
- driver normalization  
- exception wrapping (RepositoryException)  
- logging (if logger provided)  
- filtering  
- hydration (if hydrator provided)

### ✔ Provide protected helpers:

- `adapter(): AdapterInterface`  
- `driver(): object`  
- `normalizeDriver(object $driver): string`  
- `wrapDriverException(Throwable $e, string $operation): never`  

### ✔ MUST NOT:

- assume driver type  
- skip normalization  
- access Fake classes  
- create driver instances  
- store driver statically  
- run raw SQL / Redis / Mongo commands without normalization  

---

# 🟥 BaseMySQLRepository Contract

BaseMySQLRepository MUST support:

### Drivers:
- PDO  
- Doctrine\DBAL\Connection  
- FakeStorageLayer (mysql/dbal)

### Required Operations:
- select  
- insert  
- update  
- delete  
- find / findOne / findMany  
- pagination (via maatify/common)  
- typed casting of DB values  

### MUST Handle:
- DBAL exceptions → wrap  
- PDO exceptions → wrap  
- Fake exceptions → wrap  
- driver-not-supported → invalidDriver()  

### MUST NOT:
- execute raw SQL directly  
- bypass DBAL methods  
- bypass PDO prepared statements  
- access FakeStorageLayer methods directly (done via normalization)  

---

# 🟦 BaseMongoRepository Contract

Must support:

### Drivers:
- MongoDB\Database  
- MongoDB\Collection  
- FakeStorageLayer (Mongo)

### Required Operations:
- insertOne  
- insertMany  
- updateOne  
- deleteOne  
- find  
- findOne  

### MUST:
- detect Database vs Collection  
- auto-select collection from Database  
- normalize BSON → array  
- validate filters  
- wrap BSON exceptions  

### MUST NOT:
- use raw MongoDB\Client directly  
- assume collection name from class  
- throw native Mongo exceptions  

---

# 🟥 BaseRedisRepository Contract

Must support:

### Drivers:
- Redis  
- Predis\Client  
- FakeRedisAdapter  

### Required Operations:
- get  
- set  
- del  
- incr / decr  
- hash commands  
- list commands  

### MUST:
- normalize values  
- convert Predis response → PHP types  
- wrap Redis exceptions  

### MUST NOT:
- assume command casing  
- allow raw RedisException through  

---

# 🟦 Normalization Requirements

Normalization MUST use explicit instanceof:

```

if ($driver instanceof PDO) { ... }
elseif ($driver instanceof DBAL\Connection) { ... }
elseif ($driver instanceof FakeStorageLayer) { ... }
else { throw RepositoryException::invalidDriver() }

```

### MUST NOT:
- use get_class()  
- depend on adapter route  
- depend on configuration strings  

---

# 🔶 Validation Rules

Before insert/update, repository MUST run:

- required field check  
- type enforcement  
- allowed keys check  
- nullability rules  
- filter cleanup  

Validation MUST come from maatify/common.

---

# 🟩 Filtering Rules

Filtering MUST apply to all:

```

find()
findBy()
findAll()

```

Filtering MUST:

- enforce safe fields  
- sanitize operators  
- apply limit/offset  
- apply sorting  
- cast types when needed  

Filtering MUST NOT:

- modify internal driver state  
- assume driver-specific filter syntax  
- rely on SQL fragments  

---

# 🟪 Hydration Rules

Hydration MUST:

- convert arrays → DTO objects  
- validate DTO class existence  
- validate constructor parameters  
- map snake_case → camelCase automatically  
- be fully optional  
- be deterministic  

Hydration MUST NOT:

- mutate adapter/driver state  
- perform database operations  
- modify input data  

---

# 🧭 Adapter Dependency Rules

Repositories MUST:

- receive adapters ONLY via constructor or setAdapter()  
- store adapter per instance (not static)  
- never create adapters internally  
- never modify adapter configuration  

Repositories MUST NOT:

- depend on MySQLAdapter, RedisAdapter, … specific classes  
- import FakeAdapter / FakeResolver  
- call connect/disconnect manually unless required  

---

# 📤 Return-Type Contract

Repositories MUST return ONLY:

### Allowed:

- array<string,mixed>  
- array<int,array<string,mixed>>  
- DTO  
- list<DTO>  
- scalar: int|string|bool  

### Forbidden:

- mixed  
- driver objects  
- adapter objects  
- FakeStorageLayer  
- generator objects  
- closures  
- static properties  

---

# 🛑 Forbidden Behaviors

Repositories MUST NOT EVER:

- create native drivers  
- access FakeStorageLayer directly  
- bypass driver normalization  
- use raw SQL strings  
- embed Redis/Mongo commands  
- depend on test utilities  
- depend on request/session/global state  
- store results statically  
- instantiate Validator/Hydrator internally without DI  

---

# 🧱 Extendable vs Non-Extendable Methods

### Non-overridable (final or protected internal):

- normalizeDriver()  
- wrapDriverException()  
- adapter()  
- driver()  

### Override allowed:

- findBy() with custom domain logic  
- insert(), update(), delete() with domain-specific constraints  
- hydrateRow() helper if added later  
- collectionName / tableName if needed  

---

# 🔄 Repository Lifecycle

```

__construct()
↓
setAdapter()
↓
(optional) setLogger()
(optional) setHydrator()
↓
repository method called
↓
normalize driver
execute operation
validate result
filter
hydrate
return

```

Lifecycle MUST NOT be bypassed.

---

# 🧩 Summary

This contract defines:

- what repositories MUST do  
- what repositories MUST NOT do  
- mandatory behavior  
- driver compatibility  
- validation + filtering + hydration  
- error handling  
- adapter boundaries  
- normalization rules  
- return-type guarantees  

This is an invariant architectural contract  
and MUST NOT change outside roadmap updates.
