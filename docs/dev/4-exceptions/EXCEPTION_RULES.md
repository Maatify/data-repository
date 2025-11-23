![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# ⚠️ Repository Exception Rules  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Define the required structure, formatting, metadata,  
and behavior of all RepositoryException instances.

This file ensures consistent, predictable exception handling  
across ALL repository types (MySQL, Redis, Mongo)  
for both REAL (data-adapters) and FAKE (data-fakes) drivers.

---

# 📚 Table of Contents

1. [Exception Overview](#exception-overview)  
2. [RepositoryException Structure](#repositoryexception-structure)  
3. [Required Metadata](#required-metadata)  
4. [Message Format Rules](#message-format-rules)  
5. [Standardized Error Codes](#standardized-error-codes)  
6. [Driver Name Mapping](#driver-name-mapping)  
7. [Operation Mapping](#operation-mapping)  
8. [Wrapping Rules](#wrapping-rules)  
9. [Forbidden Content](#forbidden-content)  
10. [Logging Requirements](#logging-requirements)  
11. [Factory Methods](#factory-methods)  
12. [Examples](#examples)

---

# 🟦 Exception Overview

The repository layer uses **ONLY ONE** outward-facing exception:

```

RepositoryException

```

All internal errors — validation, hydration, resolver, normalization, driver errors —  
MUST be wrapped using strict formatting rules.

This ensures:

- consistent error messages  
- safe information exposure  
- identical behavior in real/fake environments  
- predictable logging  

---

# 🧩 RepositoryException Structure

All RepositoryException instances MUST contain:

| Field     | Description                                            |
|-----------|--------------------------------------------------------|
| repo      | class name of repository                               |
| driver    | normalized driver name                                 |
| operation | operation name (select/insert/update/delete/find etc.) |
| message   | clean human-readable message                           |
| code      | numeric standardized code                              |
| context   | optional array with safe metadata                      |

These MUST be part of the exception object.

---

# 🟨 Required Metadata

Every RepositoryException MUST include:

### 1) Repository name  
`UserRepository`, `OrderRepository`, etc.

### 2) Driver name  
Normalized using mapping table below.

### 3) Operation  
select, update, delete, insert, findOne, findMany, redisGet, mongoInsert…

### 4) Clean error message  
No raw driver text.

### 5) Error code  
Selected from the official error codes (below).

### 6) Context (optional but recommended)  
Safe metadata only:
- table  
- collection  
- key  
- filter  
- id  

---

# 💬 Message Format Rules

The required exception message format:

```

[repo:$REPO] [driver:$DRIVER] [op:$OPERATION] $MESSAGE

```

Examples:

```

[repo:UserRepository] [driver:mysql-pdo] [op:select] Column not found: phone
[repo:SessionRepository] [driver:redis] [op:get] Key not found
[repo:LogsRepository] [driver:mongo] [op:find] Invalid filter value

```

### NEVER allowed:
- raw SQL query  
- raw Redis command  
- raw MongoDB query  
- internal fake driver wording  
- stack traces  
- credentials  

---

# 🔢 Standardized Error Codes

| Code | Meaning                      |
|------|------------------------------|
| 1001 | Invalid driver               |
| 1002 | Driver failure               |
| 1003 | Adapter failure              |
| 1004 | Normalization failure        |
| 1005 | Resolver routing failure     |
| 1006 | Validation error             |
| 1007 | Hydration error              |
| 1008 | Unsupported operation        |
| 1009 | Unrecoverable internal error |

Repositories MUST use these codes only.  
No project-specific codes allowed unless added to this file.

---

# 🚦 Driver Name Mapping

Driver names MUST be normalized to the following values:

| Driver Instance          | Name             |
|--------------------------|------------------|
| PDO                      | mysql-pdo        |
| Doctrine\DBAL\Connection | mysql-dbal       |
| FakeStorageLayer (mysql) | fake-mysql       |
| Redis (phpredis)         | redis            |
| Predis\Client            | predis           |
| FakeRedisAdapter         | fake-redis       |
| MongoDB\Database         | mongo-db         |
| MongoDB\Collection       | mongo-collection |
| FakeStorageLayer (mongo) | fake-mongo       |

These MUST be the only values used in exceptions.

---

# 🧭 Operation Mapping

Operation names MUST be normalized:

### MySQL
- select  
- insert  
- update  
- delete  
- find  
- findOne  
- findMany  

### Redis
- get  
- set  
- del  
- incr  
- decr  
- hget  
- hset  
- lpush  
- rpush  
- lrange  

### Mongo
- find  
- findOne  
- insertOne  
- insertMany  
- updateOne  
- deleteOne  

Custom operation names MUST NOT be introduced.

---

# 🧱 Wrapping Rules

### ✔ MUST wrap any driver exception

```

try {
$driver->select(...);
} catch (\Throwable $e) {
throw RepositoryException::driverFailure(
repo: static::class,
driver: $this->normalizeDriverName(),
operation: 'select',
message: $e->getMessage(),
previous: null
);
}

```

### ✔ MUST NOT propagate native exceptions  
(e.g., PDOException MUST never escape)

### ✔ MUST wrap validation & hydration failures  
using `validationError()` or `hydrationError()` factory methods.

### ✔ MUST NOT wrap RepositoryException again  
(Double wrapping forbidden)

---

# 🚫 Forbidden Content

Exception messages MUST NOT contain:

❌ SQL queries  
❌ Redis raw commands  
❌ Mongo pipelines  
❌ Stack traces  
❌ Connection strings  
❌ Passwords  
❌ Hostnames  
❌ Internal fake driver naming  
❌ File paths  

All forbidden content MUST be stripped before output.

---

# 📝 Logging Requirements

Repositories MUST log:

- driver failures  
- adapter failures  
- resolver failures  
- invalid driver normalization  
- validation errors  

Log messages MUST contain:

```

(repo=$REPO driver=$DRIVER op=$OPERATION) $MESSAGE

```

---

# 🏗 Factory Methods

RepositoryException MUST implement these factories:

```

invalidDriver()
driverFailure()
adapterFailure()
resolverError()
validationError()
hydrationError()
unsupportedOperation()
internalError()

```

No additional factory methods allowed without roadmap update.

---

# 📘 Examples

### MySQL driver error

```

[repo:UserRepository] [driver:mysql-pdo] [op:select] Column not found: email

```

### Redis key missing

```

[repo:CacheRepository] [driver:redis] [op:get] Key not found

```

### Mongo filter error

```

[repo:LogsRepository] [driver:mongo-db] [op:find] Invalid filter type

```

### Validation error

```

[repo:UserRepository] [driver:mysql-pdo] [op:insert] Missing required field: name

```

---

# 🧩 Summary

This document defines:

- official exception format  
- error codes  
- driver name mapping  
- operation name mapping  
- wrapping rules  
- forbidden content  
- factory methods  

No repository may throw an exception  
that does not conform to this document.

This file MUST NOT change without roadmap approval.
