![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🚨 Exception Handling Guide  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Standardize error handling across repository, driver normalization, and adapter interactions.

This document defines how exceptions MUST be raised, normalized, wrapped, and exposed  
inside `maatify/data-repository`.

Exception handling is a **core architectural pillar** because repositories sit between  
domain logic and low-level adapters.  
Incorrect exception mapping breaks the entire data layer.

---

# 📚 Table of Contents

1. [Principles of Error Handling](#principles-of-error-handling)  
2. [RepositoryException](#repositoryexception)  
3. [Driver Normalization Errors](#driver-normalization-errors)  
4. [DBAL Exception Mapping](#dbal-exception-mapping)  
5. [Mongo Errors](#mongo-errors)  
6. [Redis/Predis Errors](#redispredis-errors)  
7. [Resolver Errors](#resolver-errors)  
8. [Adapter-Level Errors](#adapter-level-errors)  
9. [Fake Testing Errors](#fake-testing-errors)  
10. [Forbidden Behaviors](#forbidden-behaviors)  
11. [Best Practices](#best-practices)

---

# 🧠 Principles of Error Handling

The repository layer MUST:

### ✔ Normalize all low-level exceptions into `RepositoryException`  
### ✔ Never leak PDO/DBAL/Mongo/Redis exceptions directly  
### ✔ Include enough detail for debugging  
### ✔ Never expose sensitive information  
### ✔ Always throw a predictable, type-safe error  
### ✔ Validate driver type before usage  
### ✔ Detect all invalid states early

---

# 💥 RepositoryException

`RepositoryException` is the **only allowed exception type**  
to leave the repository layer.

### It MUST be thrown when:

- Driver type is invalid  
- DBAL throws an exception  
- PDO errors occur  
- Redis/Predis produce invalid responses  
- MongoDB returns invalid types  
- Query arguments are invalid  
- The adapter violates expectations  
- The repository environment is corrupted  

### It MUST NOT accept `$previous`  
As per project rules:

```

RepositoryException constructor MUST NOT accept $previous parameter.

```

Reason:  
We avoid deep stacking of internal driver exceptions to keep logs clean.

---

# 🔥 Driver Normalization Errors

Repositories MUST validate driver types:

### MySQL normalization MUST throw:

```

RepositoryException("Invalid MySQL driver")

```

### Redis normalization MUST throw:

```

RepositoryException("Invalid Redis driver")

```

### Mongo normalization MUST throw:

```

RepositoryException("Invalid Mongo driver")

````

### Reason:
Drivers from data-adapters and data-fakes return different native objects.  
Repository must guarantee correct object before any operation.

---

# 🟦 DBAL Exception Mapping

Doctrine DBAL may throw many different exceptions.

**ALL DBAL exceptions MUST be wrapped into RepositoryException.**

Example:

```php
try {
    $driver->executeQuery($sql);
} catch (\Doctrine\DBAL\Exception $e) {
    throw new RepositoryException("DBAL query failed");
}
````

Forbidden:

```
throw $e;
```

or

```
throw new RepositoryException("fail", 0, $e);
```

(Third parameter is forbidden.)

---

# 🟪 Mongo Errors

MongoDB operations can fail for many reasons:

* invalid collection
* invalid filter
* BSON errors
* driver connection errors

### MUST throw:

```
RepositoryException("Mongo operation failed")
```

or specific versions:

```
RepositoryException("Invalid Mongo collection")
RepositoryException("Invalid Mongo driver")
```

---

# 🟥 Redis/Predis Errors

Redis and Predis differ in:

* return types
* error codes
* string results vs integers
* missing keys behavior

Repository MUST detect:

* invalid responses
* unexpected types
* unavailable key
* unsupported command

### Example:

```
if (!is_int($result)) {
    throw new RepositoryException("Redis: unexpected result type");
}
```

---

# 🟦 Resolver Errors

RepositoryResolver MUST throw RepositoryException when:

* Route not found
* Adapter not registered
* Adapter does not implement AdapterInterface
* getDriver() returns invalid types

Example:

```
throw new RepositoryException("Adapter resolution failed");
```

---

# 🔄 Adapter-Level Errors

Adapters from data-adapters may throw exceptions.

These MUST NOT leak.

Repository MUST do:

```
try {
   $adapter->connect();
} catch (\Throwable) {
   throw new RepositoryException("Adapter connection failed");
}
```

---

# 🧪 Fake Testing Errors

Fake adapters from data-fakes may simulate:

* latency
* failures
* exceptions

Repository MUST treat fake failures **exactly like real failures**:

→ both fake and real MUST end in `RepositoryException`.

---

# ❌ Forbidden Behaviors

The repository layer MUST NOT:

* throw PDOException
* throw DBALException
* throw MongoDB\Driver exceptions
* throw RedisException
* pass-through adapter exceptions
* use FakeRepository
* assume array shapes
* assume keys exist
* assume result types
* use `getConnection()` instead of `getDriver()`
* swallow exceptions silently

---

# ⭐ Best Practices

### ✔ Always validate inputs

### ✔ Always type-check driver before use

### ✔ Always ensure correct collection in Mongo

### ✔ Always check Redis return types

### ✔ Always wrap errors in RepositoryException

### ✔ Always test normalization with Fake + Real drivers

### ✔ Always log through PSR-3 logger

---

# 🧩 Summary

Exception handling in this package ensures:

* strict behavior
* deterministic flow
* predictable errors
* safe abstraction over low-level drivers
* perfect compatibility with real adapters
* consistency between fake and real tests

This file MUST be followed during implementation
and MUST NOT change except through roadmap updates.
