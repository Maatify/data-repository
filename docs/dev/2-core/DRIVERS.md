![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🧩 Driver Normalization Guide  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative Document  
**Purpose:** Define how BaseRepository normalizes ALL drivers (real + fake) consistently.

This document is the **single source of truth** for how `BaseRepository`,  
`BaseMySQLRepository`, `BaseMongoRepository`, and `BaseRedisRepository`  
must interpret drivers returned from:

- `maatify/data-adapters` (REAL drivers)  
- `maatify/data-fakes` (FAKE drivers)  
- Through `AdapterInterface::getDriver()` from `maatify/common`

No repository may assume a driver type.  
Normalization MUST always happen at runtime using `instanceof`.

---

# ⚠️ **Critical Architectural Notes (New Additions)**

### ✔ 1. FakeRepository is Explicitly Forbidden
`FakeRepository` from data-fakes MUST NOT be used inside data-repository.  
Only FakeAdapters and FakeEnvironment are acceptable for testing.  
Repository logic must always be validated against **BaseRepository**,  
never against FakeRepository implementations.

### ✔ 2. Driver MUST be accessed using `getDriver()` ONLY  
Repositories are forbidden from using `AdapterInterface::getConnection()`.  
The only allowed call is:

```php
$driver = $adapter->getDriver();
````

This ensures that normalization rules stay consistent across all drivers.

### ✔ 3. Repositories MUST NOT Assume Array Shapes

All driver results (PDO, DBAL, MongoDB, Redis, FakeStorageLayer) must be treated
as **untrusted inputs**. No repository may assume:

* specific keys exist
* specific data shape
* serialization style
* field types

All reads must validate returned data before usage.

---

# 📚 Table of Contents

1. [MySQL / DBAL Matrix](#mysql-dbal-matrix)
2. [Redis / Predis Matrix](#redis--predis-matrix)
3. [MongoDB Matrix](#mongodb-matrix)
4. [Normalization Decision Tree](#normalization-decision-tree)
5. [Testing Matrix (Fake vs Real)](#testing-matrix-fake-vs-real)
6. [Common Shared Components](#common-shared-components)

---

# 🟩 MySQL / DBAL Matrix

The MySQL repository must support **three types** of drivers:

| Source        | Type | Driver Returned                                | Expected Operations                            | Repository          |
|---------------|------|------------------------------------------------|------------------------------------------------|---------------------|
| data-adapters | REAL | `PDO`                                          | prepare, execute, fetch, lastInsertId          | BaseMySQLRepository |
| data-adapters | REAL | `Doctrine\DBAL\Connection`                     | executeQuery, fetchAssociative, insert, update | BaseMySQLRepository |
| data-fakes    | FAKE | `FakeStorageLayer` (from FakeMySQLAdapter)     | select, insert, update, delete                 | BaseMySQLRepository |
| data-fakes    | FAKE | `FakeStorageLayer` (from FakeMySQLDbalAdapter) | fetchAll, fetchOne, insert                     | BaseMySQLRepository |

### ✔ Normalization Rules

```php
$driver = $adapter->getDriver();

if ($driver instanceof PDO) {
    // PDO pipeline
} 
elseif ($driver instanceof Doctrine\DBAL\Connection) {
    // DBAL pipeline
} 
elseif ($driver instanceof FakeStorageLayer) {
    // Fake MySQL / Fake DBAL pipeline
} 
else {
    throw new RepositoryException('Invalid MySQL driver');
}
```

---

# 🟥 Redis / Predis Matrix

Repository must accept **three** Redis-compatible drivers:

| Source        | Type | Driver Returned                  | Expected Operations        | Repository          |
|---------------|------|----------------------------------|----------------------------|---------------------|
| data-adapters | REAL | `Redis` (phpredis)               | get, set, incr, hGet, hSet | BaseRedisRepository |
| data-adapters | REAL | `Predis\Client`                  | get, set, incr, hget, hset | BaseRedisRepository |
| data-fakes    | FAKE | `FakeRedisAdapter → FakeStorage` | get, set, incr, hget, hset | BaseRedisRepository |

### ✔ Normalization Rules

```php
if ($driver instanceof Redis) {
    // phpredis
}
elseif ($driver instanceof Predis\Client) {
    // Predis
}
elseif ($driver instanceof FakeRedisAdapter) {
    // Fake Redis + Fake Predis
}
else {
    throw new RepositoryException('Invalid Redis driver');
}
```

---

# 🟦 MongoDB Matrix

MongoDB has two real drivers and one fake driver:

| Source        | Type | Driver Returned                       | Expected Operations                            | Repository          |
|---------------|------|---------------------------------------|------------------------------------------------|---------------------|
| data-adapters | REAL | `MongoDB\Database`                    | selectCollection, run commands                 | BaseMongoRepository |
| data-adapters | REAL | `MongoDB\Collection`                  | find, findOne, insertOne, updateOne            | BaseMongoRepository |
| data-fakes    | FAKE | `FakeStorageLayer` (FakeMongoAdapter) | insertOne, findOne, updateOne, deleteOne, find | BaseMongoRepository |

### ✔ Normalization Rules

```php
if ($driver instanceof MongoDB\Database) {
    // normalize to collection
}
elseif ($driver instanceof MongoDB\Collection) {
    // operate directly
}
elseif ($driver instanceof FakeStorageLayer) {
    // fake mongodb
}
else {
    throw new RepositoryException('Invalid Mongo driver');
}
```

---

# 🌲 Normalization Decision Tree

```
AdapterInterface::getDriver()
            ↓

if MySQL:
    if PDO → BaseMySQLRepository
    if DBAL → BaseMySQLRepository
    if FakeStorageLayer → BaseMySQLRepository

else if Redis:
    if Redis → BaseRedisRepository
    if Predis → BaseRedisRepository
    if FakeRedis → BaseRedisRepository

else if Mongo:
    if Database → BaseMongoRepository
    if Collection → BaseMongoRepository
    if FakeStorageLayer → BaseMongoRepository

else:
    throw RepositoryException
```

---

# 🧪 Testing Matrix (Fake vs Real)

Repository tests **must** validate BOTH behavior and normalization
using *both fake and real* adapters.

## ✔ Fake Tests (from data-fakes)

| Driver      | Fake Used                               |
|-------------|-----------------------------------------|
| MySQL       | FakeMySQLAdapter / FakeMySQLDbalAdapter |
| Redis       | FakeRedisAdapter / FakePredisAdapter    |
| Mongo       | FakeMongoAdapter                        |
| Resolver    | FakeResolver                            |
| Environment | FakeEnvironment (fixtures + snapshots)  |

## ✔ Real Integration Tests (from data-adapters)

| Driver | Real Adapter                 |
|--------|------------------------------|
| MySQL  | PDOAdapter + DBALAdapter     |
| Redis  | RedisAdapter + PredisAdapter |
| Mongo  | MongoAdapter                 |

---

# 🟨 Common Shared Components

| Component              | Source         | Purpose                    |
|------------------------|----------------|----------------------------|
| AdapterInterface       | maatify/common | unify adapter abstraction  |
| RepositoryInterface    | maatify/common | unify repository structure |
| PaginationDTO          | maatify/common | consistent pagination      |
| Validation             | maatify/common | validate insert/update     |
| Filters                | maatify/common | filtering in find/findBy   |
| LoggerInterface        | psr-logger     | PSR-3 logging              |
| EnvHelper / PathHelper | bootstrap      | environment + paths        |
| IntegrationValidator   | bootstrap      | ensure correct DI          |

---

# ✅ Final Notes

* **Repository layer never creates any driver.**
* **Fake drivers NEVER exist inside this library.**
* **FakeRepository MUST NOT be used here.**
* **All fake testing MUST come from data-fakes.**
* **All real testing MUST come from data-adapters.**
* **Normalization MUST always be explicit, never implicit.**
* **All driver outputs MUST be treated as untrusted.**

This file is mandatory for Phase 2 implementation
and MUST NOT be modified except through roadmap updates.

