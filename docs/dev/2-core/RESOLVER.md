![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🔎 RepositoryResolver Architecture Guide  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Define how repository classes obtain and initialize their adapters through Resolver logic.

The RepositoryResolver is the **dependency injection gateway**  
responsible for connecting repositories to adapters in a safe, consistent,  
and unified way across the entire ecosystem.

This document defines:

- The responsibilities of the resolver  
- The allowed adapter types  
- How routing works  
- How fake vs real adapters are handled  
- Validation expectations  
- Forbidden behaviors  
- Testing rules  

---

# 📚 Table of Contents

1. [Overview](#overview)  
2. [Responsibilities](#responsibilities)  
3. [Adapter Resolution Flow](#adapter-resolution-flow)  
4. [Validation Rules](#validation-rules)  
5. [Allowed Adapter Types](#allowed-adapter-types)  
6. [Driver Extraction Rules](#driver-extraction-rules)  
7. [Fake vs Real Behavior](#fake-vs-real-behavior)  
8. [Resolver Errors](#resolver-errors)  
9. [Best Practices](#best-practices)  
10. [Forbidden Behaviors](#forbidden-behaviors)  
11. [Testing Strategy](#testing-strategy)

---

# 🧭 Overview

Repositories do **not** create adapters.  
Repositories do **not** know how to connect to PDO, MongoDB, DBAL, or Redis.

Instead:

```

Repository → RepositoryResolver → AdapterInterface → Driver

```

The resolver acts as a **factory + validator + connector**,  
ensuring every repository receives the correct adapter instance  
for its assigned data source.

---

# 🧱 Responsibilities

RepositoryResolver MUST:

### ✔ Resolve adapter instances based on route or repository identifier  
### ✔ Ensure the adapter implements AdapterInterface  
### ✔ Connect the adapter if required  
### ✔ Validate the driver before repository usage  
### ✔ Reject invalid drivers  
### ✔ Work equally with real and fake adapters  
### ✔ Provide deterministic behavior for tests  
### ✔ Prevent direct driver injection  

---

# 🔁 Adapter Resolution Flow

A typical resolution pipeline:

```

repository → resolver → adapter → driver → repository logic

```

### Detailed Steps:

1. **Repository requests its adapter**
```

$adapter = $resolver->resolve($route);

```

2. **Resolver locates the registered adapter**
- Based on route/identifier (e.g., `"mysql.main"`, `"mongo.analytics"`)

3. **Verify adapter implements AdapterInterface**

4. **Optionally auto-connect**
- If the adapter is not connected yet

5. **Retrieve driver through getDriver()**

6. **Driver is validated**
- PDO / DBAL / Redis / Predis / Mongo / FakeStorageLayer

7. **Repository is now ready to operate**

---

# 🔐 Validation Rules

Resolver MUST enforce:

### ✔ Adapter MUST implement AdapterInterface  
### ✔ Driver MUST be one of:  
- PDO  
- Doctrine DBAL Connection  
- Redis  
- Predis Client  
- MongoDB Database  
- MongoDB Collection  
- FakeStorageLayer (from data-fakes)

### ✔ Invalid driver MUST throw RepositoryException  
### ✔ Missing adapter MUST throw RepositoryException  
### ✔ Invalid route MUST throw RepositoryException  
### ✔ Adapter connection MUST be validated with healthCheck()

---

# 🟦 Allowed Adapter Types

### From `maatify/data-adapters` (REAL)
- `PDOAdapter`
- `DBALAdapter`
- `RedisAdapter`
- `PredisAdapter`
- `MongoAdapter`

### From `maatify/data-fakes` (FAKE, tests only)
- `FakeMySQLAdapter`
- `FakeMySQLDbalAdapter`
- `FakeRedisAdapter`
- `FakePredisAdapter`
- `FakeMongoAdapter`
- `FakeResolver`

### Universal contract:
All of them MUST implement:

```

Maatify\Common\Contracts\Adapter\AdapterInterface

```

---

# 🔍 Driver Extraction Rules

Resolver MUST enforce:

```

$driver = $adapter->getDriver();

```

Repositories MUST NOT call `getConnection()`.

This ensures consistent normalization across:

- PDO
- DBAL
- Redis
- Predis
- Mongo
- Fake drivers

Resolver is the **gatekeeper** that ensures no invalid driver  
reaches the repository.

---

# 🔁 Fake vs Real Behavior

### ✔ Resolver MUST behave identically with both:

| Mode | Behavior |
|------|----------|
| Fake | Deterministic storage, simulated drivers |
| Real | Full adapter + native driver bridge |

Resolver MUST NOT:

- distinguish fake/real by name  
- apply different behavior  
- implement branching logic based on adapter type  

Everything is unified through AdapterInterface.

---

# 🚨 Resolver Errors

The resolver MUST wrap all errors into **RepositoryException**:

### ❌ Route not found

```

RepositoryException("Adapter route not found")

```

### ❌ Adapter does not implement AdapterInterface

```

RepositoryException("Invalid adapter: must implement AdapterInterface")

```

### ❌ Driver returned an unsupported type

```

RepositoryException("Invalid driver type returned from adapter")

```

### ❌ Failed to connect

```

RepositoryException("Adapter connection failed")

```

### ❌ Failed healthCheck

```

RepositoryException("Adapter health check failed")

```

---

# ⭐ Best Practices

- Keep resolver logic simple and deterministic  
- Store adapter mapping in a single configuration source  
- Ensure repository architecture does not bypass the resolver  
- Use route-based naming like:  
```

mysql.main
redis.cache
mongo.analytics

```
- Use FakeResolver for fake testing  
- Use real Resolver for integration tests  

---

# ❌ Forbidden Behaviors

Resolver MUST NOT:

- Create PDO/Mongo/Redis/DBAL objects directly  
- Instantiate drivers manually  
- Allow repositories to inject drivers  
- Allow raw driver access  
- Bypass AdapterInterface  
- Allow FakeRepository in any form  
- Use getConnection()  
- Guess driver type based on repo name  

---

# 🧪 Testing Strategy

### ✔ Fake Tests
Use:

- FakeResolver
- FakeMySQLAdapter
- FakeRedisAdapter
- FakeMongoAdapter

Goals:

- Validate adapter lookup
- Validate route resolution
- Validate driver extraction
- Validate health check enforcement

### ✔ Real Integration Tests
Use:

- real Resolver config
- real PDO / DBAL / Redis / Predis / Mongo adapters

Goals:

- Confirm real driver normalization
- Confirm health checks match real behavior
- Confirm repository receives correct drivers for its route

---

# 🧩 Summary

RepositoryResolver is the **single source of adapter initialization**  
for all repositories.  
It MUST enforce:

- correct adapter type  
- correct driver type  
- correct connection state  
- correct error normalization  
- correct routing  
- correct fake/real parity  

This document MUST be followed during implementation  
and MUST NOT change except through roadmap updates.
