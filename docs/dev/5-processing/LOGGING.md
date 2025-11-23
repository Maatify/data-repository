![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 📝 Logging Rules  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Define a unified, deterministic logging strategy for all Base* repositories using `maatify/psr-logger`.

---

# 📚 Table of Contents

1. [Logging Philosophy](#logging-philosophy)  
2. [Logger Source](#logger-source)  
3. [Injection Rules](#injection-rules)  
4. [When to Log](#when-to-log)  
5. [What to Log](#what-to-log)  
6. [Log Levels](#log-levels)  
7. [Logging Format](#logging-format)  
8. [Driver-Level Logging](#driver-level-logging)  
9. [Exception Logging](#exception-logging)  
10. [Fake Logging](#fake-logging)  
11. [Security Logging](#security-logging)  
12. [Testing Logging](#testing-logging)  
13. [Summary](#summary)

---

# 🧠 Logging Philosophy

The repository layer MUST:

- ✔ Provide full transparency  
- ✔ Log every driver interaction  
- ✔ Log failures deterministically  
- ✔ Log normalization decisions  
- ✔ Never leak unsafe data  
- ✔ Use PSR-3 logging only  
- ✔ Centralize logging format  

Repositories MUST NOT print, echo, var_dump, or write to custom log files.

All logging MUST happen through:

```

maatify/psr-logger

```

---

# 🟦 Logger Source

Repositories MUST depend on:

```

Psr\Log\LoggerInterface

```

Actual implementation MUST come from:

```

maatify/psr-logger

```

Fake tests MUST use:

```

FakeLogger (deterministic logger)

````

---

# 🟩 Injection Rules

Repository MUST support logger injection through:

```php
public function __construct(AdapterInterface $adapter, ?LoggerInterface $logger = null)
````

* If logger is **null** → repository MUST auto-load default logger from maatify/psr-logger
* Logger MUST be optional
* Logger MUST NOT be mandatory for tests
* Logger MUST NOT throw exceptions

---

# 🟧 When to Log

Repository MUST log during:

### 1. Adapter connection

* connect
* disconnect
* reconnect
* health check

### 2. Query execution

* before driver call
* after driver call (success)
* after driver failure (exception)

### 3. Normalization

* driver type detected (PDO/DBAL/Mongo/Redis/Fake)
* filter normalization
* pagination normalization
* hydration decisions

### 4. Insert/Update/Delete

* payload received
* driver response
* row count updates

### 5. Validation

* validation start
* validation errors

---

# 🟥 What to Log

Repository MUST log:

| Event             | Required? | Example                                     |
|-------------------|-----------|---------------------------------------------|
| Driver detection  | YES       | Driver detected: PDO                        |
| Query start       | YES       | Executing select on table users             |
| Query end         | YES       | Query OK: 3 rows                            |
| Inputs            | YES       | filters, updates, pagination (safe content) |
| Hydration         | YES       | Hydrating 5 records into UserDTO            |
| Validation errors | YES       | Missing required field: email               |
| Driver errors     | YES       | DBALException: connection lost              |

Repository MUST NOT log:

❌ passwords  
❌ env variables  
❌ full exception traces in production  
❌ raw SQL  
❌ raw Mongo DB commands  
❌ Redis AUTH data

---

# 🟦 Log Levels

Repository MUST use the following:

| Level       | Purpose                                                |
|-------------|--------------------------------------------------------|
| `debug`     | low-level operations (sorting, filters, normalization) |
| `info`      | normal repository operations                           |
| `notice`    | unusual but not harmful behavior                       |
| `warning`   | recoverable issues                                     |
| `error`     | driver errors, failed queries                          |
| `critical`  | adapter initialization failure                         |
| `alert`     | catastrophic failures                                  |
| `emergency` | system-wide unrecoverable state                        |

Driver errors MUST log at least `error` level.

---

# 📏 Logging Format

Standard PSR-3 format MUST be:

```php
$this->logger->info(
    'Repository action: select',
    [
        'repository' => static::class,
        'driver' => get_class($driver),
        'filters' => $filters,
        'options' => $options,
        'duration_ms' => $duration,
    ]
);
```

Fields MUST follow the same keys in all repositories.

---

# 🔌 Driver-Level Logging

Real adapters MUST log:

* Connect
* Disconnect
* Ping (healthCheck)
* Reconnect attempts
* Query execution

Fake adapters MUST log:

* in-memory operations
* table access
* filtering logic
* insert/update/delete operations

Fake logger output MUST be deterministic.

---

# 🚫 Exception Logging

Before throwing RepositoryException, repository MUST:

1. Log error with `error` level
2. Include:

    * exception class
    * message
    * operation
    * filters/payload
    * driver type

Repository MUST NOT expose sensitive values.

---

# 🟣 Fake Logging

FakeLogger MUST be used ONLY inside tests:

* MUST store logs in local array
* MUST allow asserting logs
* MUST NOT use external filesystem
* MUST NOT use timestamps to avoid nondeterminism

---

# 🛡 Security Logging

Sensitive logging MUST be masked:

| Field              | Masking              |
|--------------------|----------------------|
| email              | user@example[HIDDEN] |
| password           | NEVER LOG            |
| token              | NEVER LOG            |
| connection string  | mask password        |
| redis key patterns | allow ONLY safe keys |

---

# 🧪 Testing Logging

Tests MUST assert:

* logger receives correct events
* logger receives correct context keys
* FakeLogger captures logs deterministically
* errors logged before exceptions
* logs do NOT contain sensitive content
* normalization logs exist

---

# 🧩 Summary

Logging inside the repository layer MUST be:

* Unified (one format)
* Deterministic
* PSR-3 compliant
* No side effects
* No sensitive values
* Compatible with Fake + Real drivers
* Always active (unless silent mode is enabled internally)

This file MUST NOT be edited without roadmap approval.
