![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🧩 Drivers Matrix (Real + Fake)  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative Document  
**Purpose:** Provide the FULL driver compatibility matrix used by the repository layer.  
It defines every acceptable driver type for MySQL, Redis, and MongoDB —  
both REAL (from data-adapters) and FAKE (from data-fakes) —  
and how they map to the BaseRepository classes.

---

# 📚 Table of Contents

1. [Overview](#overview)  
2. [MySQL / DBAL Matrix](#mysql--dbal-matrix)  
3. [Redis / Predis Matrix](#redis--predis-matrix)  
4. [MongoDB Matrix](#mongodb-matrix)  
5. [Fake Drivers Summary](#fake-drivers-summary)  
6. [Repository Mapping](#repository-mapping)  
7. [Error Rules](#error-rules)

---

# 🟩 Overview

Every repository works with drivers returned ONLY through:

```

AdapterInterface::getDriver()

````

from:

- **maatify/data-adapters** (REAL)
- **maatify/data-fakes** (FAKE)
- **maatify/common** (interfaces only)

The repository layer MUST normalize drivers using explicit `instanceof` checks  
and MUST NEVER assume a driver type by configuration or route.

---

# 🟦 MySQL / DBAL Matrix

| Source        | Category | Driver Type                     | Normalized As | Repository Pipeline |
|---------------|----------|---------------------------------|---------------|---------------------|
| data-adapters | REAL     | `PDO`                           | MySQL-PDO     | BaseMySQLRepository |
| data-adapters | REAL     | `Doctrine\DBAL\Connection`      | MySQL-DBAL    | BaseMySQLRepository |
| data-fakes    | FAKE     | `FakeStorageLayer` (MySQL)      | Fake-MySQL    | BaseMySQLRepository |
| data-fakes    | FAKE     | `FakeStorageLayer` (DBAL style) | Fake-DBAL     | BaseMySQLRepository |

### ✔ Normalization Rules

```php
if ($driver instanceof PDO) {
    // PDO pipeline
} elseif ($driver instanceof Doctrine\DBAL\Connection) {
    // DBAL pipeline
} elseif ($driver instanceof FakeStorageLayer) {
    // Fake MySQL/DBAL pipeline
} else {
    throw new RepositoryException('Invalid MySQL driver');
}
````

---

# 🟥 Redis / Predis Matrix

| Source        | Category | Driver Type        | Normalized As  | Repository Pipeline |
|---------------|----------|--------------------|----------------|---------------------|
| data-adapters | REAL     | `Redis`            | Redis-phpredis | BaseRedisRepository |
| data-adapters | REAL     | `Predis\Client`    | Redis-predis   | BaseRedisRepository |
| data-fakes    | FAKE     | `FakeRedisAdapter` | Fake-Redis     | BaseRedisRepository |

### ✔ Normalization Rules

```php
if ($driver instanceof Redis) {
    // phpredis
} elseif ($driver instanceof Predis\Client) {
    // Predis
} elseif ($driver instanceof FakeRedisAdapter) {
    // Fake Redis
} else {
    throw new RepositoryException('Invalid Redis driver');
}
```

---

# 🟦 MongoDB Matrix

| Source        | Category | Driver Type          | Normalized As    | Repository Pipeline |
|---------------|----------|----------------------|------------------|---------------------|
| data-adapters | REAL     | `MongoDB\Database`   | Mongo-Database   | BaseMongoRepository |
| data-adapters | REAL     | `MongoDB\Collection` | Mongo-Collection | BaseMongoRepository |
| data-fakes    | FAKE     | `FakeStorageLayer`   | Fake-Mongo       | BaseMongoRepository |

### ✔ Normalization Rules

```php
if ($driver instanceof MongoDB\Database) {
    // resolve collection
} elseif ($driver instanceof MongoDB\Collection) {
    // operate directly
} elseif ($driver instanceof FakeStorageLayer) {
    // Fake Mongo
} else {
    throw new RepositoryException('Invalid Mongo driver');
}
```

---

# 🧪 Fake Drivers Summary

These drivers MUST ONLY be used in tests:

| Fake Type        | Real Equivalent Simulated   |
|------------------|-----------------------------|
| FakeMySQLAdapter | PDO MySQL                   |
| FakeMySQLDbal    | DBAL Connection             |
| FakeRedisAdapter | Redis + Predis              |
| FakeMongoAdapter | MongoDB\Database/Collection |
| FakeResolver     | Real Resolver               |
| FakeEnvironment  | Real services initializer   |

### ❗ IMPORTANT RULE

Production code MUST NEVER reference or import_fake classes.
Fake drivers belong exclusively to:

```
maatify/data-fakes
tests/
```

---

# 🧭 Repository Mapping

| Repository Class        | Acceptable Drivers                                     |
|-------------------------|--------------------------------------------------------|
| BaseMySQLRepository     | PDO, DBAL Connection, FakeStorageLayer                 |
| BaseRedisRepository     | Redis, Predis\Client, FakeRedisAdapter                 |
| BaseMongoRepository     | MongoDB\Database, MongoDB\Collection, FakeStorageLayer |
| BaseRepository (common) | All above, depending on concrete implementation        |

Each Base* repository MUST:

* normalize driver
* use correct pipeline
* wrap failures in `RepositoryException`
* NOT assume driver type
* NOT depend on Fake classes

---

# ⚠ Error Rules

Repository MUST throw `RepositoryException` when:

* Driver type is unsupported
* Adapter returns unexpected object
* Fake driver leaks into production
* Driver pipeline fails
* Validation fails
* Normalization branch is unreachable
* Untrusted array shapes are returned

Raw driver exceptions MUST NEVER propagate to the user.

---

# 🧩 Summary

This matrix is the **single source of truth** for:

* which drivers are allowed
* how repositories normalize them
* how fake tests work
* how real tests work
* how data sources map to repository pipelines

This file MUST NOT be changed without roadmap approval
and MUST be followed exactly during Phase 2 implementation.

