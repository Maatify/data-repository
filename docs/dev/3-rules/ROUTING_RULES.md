![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🔀 Routing Rules (Resolver Architecture Specification)  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Provide the full specification for how repository routes  
are interpreted, validated, and resolved into adapters through the Resolver Layer.

This document ensures:

- consistent adapter selection  
- predictable routing  
- strict separation between repository ↔ resolver  
- no implicit logic  
- compatibility with both REAL and FAKE environments  

---

# 📚 Table of Contents

1. [Overview](#overview)  
2. [What is a Route?](#what-is-a-route)  
3. [Route Format](#route-format)  
4. [Route Examples](#route-examples)  
5. [Resolver Responsibilities](#resolver-responsibilities)  
6. [Resolver MUST Rules](#resolver-must-rules)  
7. [Resolver MUST NOT Rules](#resolver-must-not-rules)  
8. [Adapter Resolution Process](#adapter-resolution-process)  
9. [Route Normalization](#route-normalization)  
10. [Error Rules](#error-rules)  
11. [Fake vs Real Resolver](#fake-vs-real-resolver)  
12. [Testing Rules](#testing-rules)  
13. [Forbidden Behaviors](#forbidden-behaviors)

---

# 🟦 Overview

The Repository Layer NEVER chooses drivers directly.

Instead, repositories rely on **Resolver → Adapter → Driver**.

```

Repository
↓
RepositoryResolver / FakeResolver
↓
AdapterInterface
↓
Driver (PDO, DBAL, Redis, Predis, Mongo, Fake)

```

Every repository MUST specify a **route** string  
that tells the Resolver *which adapter* it needs.

---

# 🧩 What is a Route?

A route is a **string identifier** representing a data-connection entry:

Examples:

```

mysql.default
mysql.analytics
redis.cache
redis.sessions
mongo.primary
mongo.logs

```

These names are configuration-driven, NOT hardcoded.

---

# 🟩 Route Format

The allowed route format is:

```

<driver-type>.<connection-name>

```

Where:

| Part | Meaning |
|------|---------|
| driver-type | mysql / redis / mongo |
| connection-name | identifier from env/config |

### Examples:

```

mysql.default
mysql.reporting
redis.cache
mongo.events
mongo.analytics

```

---

# 🟧 Route Rules

### 1) MUST contain a dot  
No single-word routes.

### 2) driver-type MUST be one of:

```

mysql
redis
mongo

```

### 3) connection-name MUST be alphanumeric + underscores  

### 4) Both sides are case-insensitive but normalized internally to lowercase.

---

# 🟫 Route Examples

### ✔ Valid Routes

```

mysql.default
mysql.readonly
redis.cache
redis.sessions
mongo.primary
mongo.analytics

```

### ❌ Invalid Routes

```

mysql
default
mysql-default
MYSQL.DEFAULT.CONN
redis cache

```

---

# 🟦 Resolver Responsibilities

The Resolver MUST:

- parse route  
- validate format  
- locate correct adapter configuration  
- instantiate correct adapter (REAL)  
- or return corresponding fake adapter (FAKE tests)  
- ensure adapter implements AdapterInterface  
- return the adapter instance to repository  
- not perform any driver operations  
- not call driver methods  
- not perform connection logic beyond adapter duties  

---

# 🟩 Resolver MUST Rules

The Resolver MUST:

✔ Validate route format  
✔ Normalize driver-type  
✔ Normalize connection-name  
✔ Lookup adapter using configuration  
✔ Return an AdapterInterface instance  
✔ Defer all failing logic to RepositoryException  
✔ Work identically in real/fake mode  
✔ Throw clean resolver error on invalid route  

---

# 🟥 Resolver MUST NOT Rules

The Resolver MUST NOT:

❌ Instantiate drivers directly  
❌ Return FakeStorageLayer  
❌ Return raw PDO/Redis/Mongo objects  
❌ Make assumptions about repository names  
❌ Interpret domain logic  
❌ Read environment variables directly (adapter does that)  
❌ Modify global state  
❌ Cache adapters statically (unless in data-adapters config)  

---

# 🔄 Adapter Resolution Process

Standard Resolution Flow:

```

Repository calls: $this->resolver->resolve($route)
↓
Validate route format
↓
Extract driver-type + connection-name
↓
Find matching adapter class
↓
Instantiate adapter
↓
Ensure adapter implements AdapterInterface
↓
Return adapter instance

```

---

# 🧭 Route Normalization

All routes MUST be normalized:

| Input | Normalized |
|--------|------------|
| "MySQL.Default" | "mysql.default" |
| "REDIS.Cache" | "redis.cache" |
| "mongo.Primary" | "mongo.primary" |

Normalization MUST NOT change connection-name semantics.

---

# 🛑 Error Rules

The Resolver MUST throw `RepositoryException::resolverError()` when:

- route missing dot  
- unsupported driver-type  
- unsupported connection-name  
- adapter configuration missing  
- adapter class invalid  
- adapter does NOT implement AdapterInterface  
- adapter instantiation fails  
- adapter returns null  
- circular dependency detected  

---

# 🧪 Fake vs Real Resolver

### REAL resolver (data-adapters):
- loads config  
- returns real adapters  
- adapter → real driver

### FAKE resolver (data-fakes):
- does NOT load config  
- uses in-memory route map  
- returns fake adapters ONLY  
- adapter → fake driver  
- intended for deterministic testing  

Both MUST be API-identical.

---

# 🧪 Testing Rules

### FakeResolver MUST:

- reject invalid routes  
- return fake implementations ONLY  
- ensure FakeAdapter implements AdapterInterface  
- simulate missing route scenarios

### Real tests MUST:

- validate route → adapter → driver chain  
- validate correct driver selected  
- validate normalization  

---

# 🚫 Forbidden Behaviors

Resolvers MUST NOT:

❌ Map one route to multiple adapters  
❌ Convert routes dynamically  
❌ Guess missing segments  
❌ Perform fallback logic  
❌ Embed business rules  
❌ Embed driver-specific logic  
❌ Store internal state for reuse unless explicitly configured  
❌ Accept non-string routes  

---

# 🧩 Summary

This document defines:

- full routing syntax  
- resolver behavior  
- adapter resolution flow  
- normalization rules  
- allowed driver-types  
- driver/fake resolution boundaries  
- error-handling strategy  
- testing rules  
- forbidden behaviors  

Repositories MUST rely on this routing contract  
and MUST NOT interact with adapters outside this flow.
