![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🧪 Testing Strategy Guide  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Define the complete testing methodology for repositories using both real and fake adapters.

This document explains **what must be tested**, **how**, **using which tools**,  
and **what is forbidden** during testing.

It is the mandatory reference for all future test suites in this package.

---

# 📚 Table of Contents

1. [Testing Philosophy](#testing-philosophy)  
2. [Types of Tests](#types-of-tests)  
3. [Fake Testing (Deterministic Layer)](#fake-testing-deterministic-layer)  
4. [Real Testing (Integration Layer)](#real-testing-integration-layer)  
5. [Mandatory Rules](#mandatory-rules)  
6. [Forbidden Behaviors](#forbidden-behaviors)  
7. [Fixtures, Snapshots, and Reset](#fixtures-snapshots-and-reset)  
8. [Test Environment Structure](#test-environment-structure)  
9. [Coverage Expectations](#coverage-expectations)  
10. [Test Matrix Summary](#test-matrix-summary)

---

# 🧠 Testing Philosophy

The repository layer must be tested using **two complementary modes**:

### ✔ Fake Testing  
Deterministic, isolated, reproducible, fast.  
Ensures repository logic is correct.

### ✔ Real Testing  
Validates compatibility with real drivers.  
Ensures normalization rules match real-world behavior.

These two modes together guarantee:

- Repository logic correctness  
- Driver compatibility  
- AdapterInterface integrity  
- Behavior consistency across PDO/DBAL/Mongo/Redis/Predis/Fakes  

---

# 🧪 Types of Tests

### 1) **Unit Tests (Fake-based)**
- Test repository logic only
- Deterministic behavior using FakeAdapters
- No external databases required

### 2) **Integration Tests (Real-based)**
- Use real PDO, DBAL, Redis, Predis, Mongo connections
- Validate driver normalization

### 3) **Resolver Tests**
- Ensure RepositoryResolver selects correct adapter
- Ensure invalid routes throw exceptions

### 4) **Exception Tests**
- Validate that incorrect drivers trigger RepositoryException
- Validate DBAL → RepositoryException mapping
- Validate Mongo normalization errors

### 5) **Behavioral Tests**
- Pagination correctness  
- Filtering  
- Hydration  
- Update/Delete data flow  
- Counter behavior in Redis

---

# 🟦 Fake Testing (Deterministic Layer)

Fake testing uses:

| Component            | Source     |
|----------------------|------------|
| FakeMySQLAdapter     | data-fakes |
| FakeMySQLDbalAdapter | data-fakes |
| FakeRedisAdapter     | data-fakes |
| FakePredisAdapter    | data-fakes |
| FakeMongoAdapter     | data-fakes |
| FakeResolver         | data-fakes |
| FakeEnvironment      | data-fakes |
| FakeStorageLayer     | data-fakes |

Fake tests MUST cover:

### ✔ CRUD operations  
### ✔ Filtering  
### ✔ Pagination  
### ✔ Exception normalization  
### ✔ RepositoryAdapter integration  
### ✔ Hydrator logic  
### ✔ Redis counters + hash ops  
### ✔ Mongo document pipelines  
### ✔ DBAL behavior  
### ✔ Commit/rollback logic if needed  
### ✔ Fixtures + snapshot resets

Fake tests **must NOT** test driver-specific behavior  
(because fakes simulate storage, not real DB engines).

---

# 🟥 Real Testing (Integration Layer)

Real tests use drivers from **maatify/data-adapters**:

| Driver           | Adapter       |
|------------------|---------------|
| PDO              | PDOAdapter    |
| DBAL             | DBALAdapter   |
| Redis (phpredis) | RedisAdapter  |
| Predis           | PredisAdapter |
| MongoDB          | MongoAdapter  |

Real tests focus on:

### ✔ Driver normalization  
### ✔ Query execution (PDO, DBAL)  
### ✔ Key-value behavior (Redis/Predis)  
### ✔ BSON normalization (MongoDB)  
### ✔ Error mapping  
### ✔ Real-world compatibility  

These tests ensure that the repository layer  
works identically with:

- real drivers  
- fake drivers  

---

# ⚠ Mandatory Rules

### ✔ 1. All repositories MUST be tested using both Fake and Real adapters  
No repository is considered complete without:

- fake tests  
- real tests  

### ✔ 2. FakeRepository is strictly forbidden  
Tests MUST NOT use FakeRepository from data-fakes.

### ✔ 3. Repository MUST use getDriver(), not getConnection()  
Tests MUST verify this behavior.

### ✔ 4. Driver assumptions are forbidden  
Tests MUST validate behavior even if:

- row keys missing  
- wrong data type  
- invalid filters  
- unexpected driver response  

### ✔ 5. Exceptions MUST be normalized  
Tests MUST ensure:

- DBALException → RepositoryException  
- Invalid driver → RepositoryException  
- Mongo invalid collection → RepositoryException  
- Redis/Predis type mismatch → RepositoryException  

---

# ❌ Forbidden Behaviors

- Using FakeRepository  
- Calling getConnection() inside repository  
- Mocking PDO/Redis directly  
- Bypassing AdapterInterface  
- Using real adapters inside Fake tests  
- Using fake adapters inside Real tests  
- Assuming driver-specific data shapes  
- Writing tests dependent on implicit behavior  

---

# 📁 Fixtures, Snapshots, and Reset

Tests MUST use:

### ✔ FakeEnvironment::beforeTest()  
To reset all drivers before each test.

### ✔ SnapshotManager  
To validate rollback and state recovery.

### ✔ JsonFixtureParser  
To load structured dataset files.

### Allowed Fixture Shapes:
- table → rows  
- collection → documents  
- redis → keys  
- dbal → associative arrays  

---

# 🗂 Test Environment Structure

Recommended structure:

```

tests/
bootstrap.php
unit/
MySQL/
Mongo/
Redis/
Resolver/
integration/
MySQL/
Redis/
Mongo/
fixtures/
mysql.json
redis.json
mongo.json

```

---

# 📊 Coverage Expectations

The repository layer MUST reach:

### ✔ Minimum 90% coverage  
### ✔ 100% coverage for BaseRepository  
### ✔ 100% coverage for normalization logic  
### ✔ 100% coverage for exception branches  
### ✔ 100% coverage for Redis/Mongo primitive operations  

---

# 📋 Test Matrix Summary

| Area                 | Fake Test              | Real Test      |
|----------------------|------------------------|----------------|
| BaseRepository       | ✔                      | ❌              |
| RepositoryResolver   | ✔                      | ✔              |
| MySQL Repository     | ✔ FakeMySQL            | ✔ PDO/DBAL     |
| Mongo Repository     | ✔ FakeMongo            | ✔ MongoAdapter |
| Redis Repository     | ✔ FakeRedis/FakePredis | ✔ Redis/Predis |
| Hydration            | ✔                      | ❌              |
| Pagination           | ✔                      | ❌              |
| Filters              | ✔                      | ❌              |
| Exception Mapping    | ✔                      | ✔              |
| Driver Normalization | ✔                      | ✔              |

---

# ✅ Final Notes

- Fake tests validate repository logic.  
- Real tests validate driver behavior.  
- Both are required for correctness.  
- No fake classes may be used in production code.  
- All tests MUST pass on GitHub CI using Docker services.  

This document defines the **official testing strategy**  
for all phases of `maatify/data-repository`.

