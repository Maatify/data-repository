![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🛑 Exception Taxonomy  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Define ALL allowed exception types, their sources,  
and how the repository layer converts or wraps errors consistently.

This is the official taxonomy for all repository-related exceptions.  
No layer may introduce new exception categories  
without roadmap approval.

---

# 📚 Table of Contents

1. [Overview](#overview)  
2. [Allowed Exception Sources](#allowed-exception-sources)  
3. [RepositoryException](#repositoryexception)  
4. [Driver-Level Exceptions](#driver-level-exceptions)  
5. [Normalization Errors](#normalization-errors)  
6. [Adapter-Level Errors](#adapter-level-errors)  
7. [Validation Errors](#validation-errors)  
8. [Hydration Errors](#hydration-errors)  
9. [Resolver Errors](#resolver-errors)  
10. [Fake vs Real Error Mapping](#fake-vs-real-error-mapping)  
11. [Forbidden Exceptions](#forbidden-exceptions)  
12. [Error Mapping Table](#error-mapping-table)  
13. [Error Format Rules](#error-format-rules)

---

# 🟦 Overview

The repository layer MUST expose **one unified error type** to the application:

```

RepositoryException

```

Everything else MUST be wrapped inside it.

Native driver errors (PDO, DBAL, Redis, Predis, MongoDB)  
MUST NEVER leak to upper layers.

Fake drivers MUST produce errors **identical** to real ones  
after mapping.

---

# 🟩 Allowed Exception Sources

Repositories may encounter exceptions from:

| Source            | Example Errors                |
|-------------------|-------------------------------|
| **PDO**           | PDOException                  |
| **DBAL**          | Doctrine\DBAL\Exception       |
| **MongoDB**       | MongoDB\Driver\Exception      |
| **Redis**         | RedisException                |
| **Predis**        | Predis\CommunicationException |
| **Adapters**      | AdapterInterface violations   |
| **Resolver**      | invalid route                 |
| **Validation**    | invalid data shape            |
| **Hydration**     | reflection errors             |
| **Normalization** | unsupported driver            |
| **Fake Drivers**  | logical simulation errors     |

All of these MUST map to RepositoryException.

---

# 🔥 RepositoryException

The **only exception type exposed externally**.

### It MUST be thrown when:

- driver type is invalid  
- adapter returns unexpected driver  
- driver operation fails  
- validation fails  
- hydration fails  
- normalization fails  
- resolver returns invalid adapter  
- unexpected data shape  
- missing required fields  
- invalid pagination  
- filters have invalid types  

### MUST contain:

- error code  
- message  
- context data  
- driver type  
- operation name  
- NEVER include raw driver exception  

---

# 🟥 Driver-Level Exceptions

Drivers may throw:

### PDO
- `PDOException`

### DBAL
- `Doctrine\DBAL\Exception`
- `Doctrine\DBAL\Driver\Exception`

### MongoDB
- `MongoDB\Driver\Exception\Exception`

### Redis (phpredis)
- `RedisException`

### Predis
- `Predis\Connection\ConnectionException`
- `Predis\Response\ServerException`

### Fake Drivers
- FakeDriverException (internal only)

All driver-level exceptions MUST be caught and wrapped.

---

# 🟧 Normalization Errors

Are thrown when:

- driver is unsupported  
- adapter returns null  
- adapter returns invalid object  
- repository calls unsupported operation on driver  
- driver pipeline is unreachable  

Mapped as:

```

RepositoryException::invalidDriver()

```

---

# 🟪 Adapter-Level Errors

Examples:

- adapter not connected  
- adapter throws during connect()  
- adapter returns invalid driver  
- adapter violates AdapterInterface contract  

Mapped as:

```

RepositoryException::adapterFailure()

```

---

# 🟨 Validation Errors

From maatify/common validator:

- missing fields  
- wrong types  
- forbidden keys  
- unsafe arrays  
- nullability violations  

Mapped as:

```

RepositoryException::validationError()

```

---

# 🔵 Hydration Errors

From Hydrator:

- missing DTO class  
- ctor mismatch  
- wrong property type  
- reflection error  
- readonly property violation  

Mapped as:

```

RepositoryException::hydrationError()

```

---

# 🟫 Resolver Errors

From Resolver:

- invalid route  
- adapter not found  
- unresolved connection  
- circular reference  

Mapped as:

```

RepositoryException::resolverError()

```

---

# 🧪 Fake vs Real Error Mapping

Fake drivers MUST throw errors compatible with real drivers.

Example:

FakeMySQLAdapter → FakeLogicException  
→ wraps to: RepositoryException::driverFailure(mysql, SELECT)

FakeRedisAdapter → FakeLogicException  
→ wraps to: RepositoryException::driverFailure(redis, GET)

FakeMongoAdapter → FakeLogicException  
→ wraps to: RepositoryException::driverFailure(mongo, FIND)

---

# 📊 Error Mapping Table

| Source        | Example                  | RepositoryException       |
|---------------|--------------------------|---------------------------|
| PDO           | PDOException             | driverFailure(mysql)      |
| DBAL          | DBALException            | driverFailure(mysql-dbal) |
| Redis         | RedisException           | driverFailure(redis)      |
| Predis        | PredisException          | driverFailure(redis)      |
| MongoDB       | MongoException           | driverFailure(mongo)      |
| FakeMySQL     | FakeException            | driverFailure(fake-mysql) |
| FakeRedis     | FakeException            | driverFailure(fake-redis) |
| FakeMongo     | FakeException            | driverFailure(fake-mongo) |
| Validation    | InvalidArgumentException | validationError           |
| Hydration     | ReflectionException      | hydrationError            |
| Resolver      | ResolverException        | resolverError             |
| Normalization | UnexpectedValueException | invalidDriver             |

---

# 📝 Error Format Rules

All RepositoryException messages MUST follow:

```

[repo:$REPO] [$DRIVER] [$OPERATION] $MESSAGE

```

Example:

```

[repo:UserRepository] [mysql-pdo] [select] Column not found: email

```

### MUST include:
- repo name  
- driver name  
- operation (insert, update, find…)  
- clean message  

### MUST NOT include:
- stack traces  
- raw driver error messages  
- credentials  
- internal fake driver text  

---

# 🧩 Summary

This file establishes the **complete** exception policy:

- Only one external exception type  
- Full wrapping rules  
- Full mapping rules  
- Fake and real treated identically  
- No new exceptions allowed  
- No raw driver leakage allowed  
- No inconsistent error messages  

This taxonomy MUST NOT change without roadmap approval.
