![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🔥 **docs/RESILIENCY_RULES.md**

**Project:** maatify/data-repository
**Version:** 1.0.0
**Status:** Authoritative
**Purpose:** Define unified resiliency architecture for all repositories.

Resiliency = **how repository reacts when the underlying driver fails.**
This includes:

* connection failures
* timeouts
* invalid results
* driver-level exceptions
* adapter disconnections
* retrying logic
* graceful degradation
* determinism between REAL + FAKE tests

This document is the OFFICIAL reference used by:

* Repository layer
* IntegrationValidator
* data-fakes (ErrorSimulator)
* data-adapters
* CI tests
* Executor Engine validation rules

---

# 📚 Table of Contents

1. [Resiliency Philosophy](#resiliency-philosophy)
2. [Failure Types](#failure-types)
3. [Mandatory Repository Behavior](#mandatory-repository-behavior)
4. [Retry Rules](#retry-rules)
5. [Timeout Rules](#timeout-rules)
6. [Driver-State Enforcement](#driver-state-enforcement)
7. [Fake vs Real Resiliency](#fake-vs-real-resiliency)
8. [Resiliency Logging](#resiliency-logging)
9. [Error Classification Table](#error-classification-table)
10. [Adapter & Driver Contracts](#adapter--driver-contracts)
11. [Integration Scenarios](#integration-scenarios)
12. [CI & Test Behavior](#ci--test-behavior)
13. [Examples](#examples)
14. [Summary](#summary)

---

# 🧠 Resiliency Philosophy

Repositories MUST be:

* **deterministic**: same input → same behavior
* **predictable**: failure modes are known
* **safe**: NO undefined driver behavior
* **isolated**: repository catches ALL driver-level exceptions
* **consistent**: fake and real act the same
* **logged**: all resiliency failures are logged
* **strict**: repository NEVER swallows driver errors silently
* **controlled**: retries have strict rules

Repository MUST protect the application from driver chaos.

---

# ⚠ Failure Types

| Category                | Examples                                        | Must Throw                    |
|-------------------------|-------------------------------------------------|-------------------------------|
| **Connection**          | cannot connect, broken pipe                     | RepositoryConnectionException |
| **Timeout**             | long-running DB query                           | RepositoryTimeoutException    |
| **Protocol Errors**     | Redis protocol mismatch, Mongo invalid response | RepositoryDriverException     |
| **Disconnected Driver** | driver returns no connection                    | RepositoryConnectionException |
| **Invalid Driver Type** | wrong driver instance                           | RepositoryDriverException     |
| **Data Integrity**      | inconsistent rows, invalid types                | RepositoryDataException       |

---

# 📌 Mandatory Repository Behavior

Every BaseRepository MUST:

### ✔ Catch ALL driver exceptions

PDOException, DBALException, MongoDBException, RedisException, Predis errors…
ALL must be normalized to:

* **RepositoryConnectionException**
* **RepositoryDriverException**
* **RepositoryTimeoutException**
* **RepositoryDataException**

### ✔ Validate driver BEFORE use

```
if (!$adapter->isConnected()) {
    throw RepositoryConnectionException
}
```

### ✔ Log ALL failures

Through unified logger.

### ✔ NEVER return mixed

ALL failure flows must throw exception.

---

# 🔁 Retry Rules

Retries MUST be:

* **off by default**
* **opt-in per repository**
* **maximum 3 attempts**
* **exponential backoff**
* **applies only to idempotent operations**
  (select, find, findBy, count)

Forbidden retries:

❌ insert
❌ update
❌ delete
❌ transactions

---

# ⏱ Timeout Rules

Repository MUST define:

```
protected int $timeoutMilliseconds = 1500;
```

Rules:

* driver-level timeout → map to RepositoryTimeoutException
* fake latency simulator MUST simulate timeout behavior
* repository must abort after timeout exceeded

---

# 🧩 Driver-State Enforcement

Before ANY operation:

```
if (!$adapter->isConnected()) {
    $adapter->connect();
    if (!$adapter->isConnected()) {
        throw RepositoryConnectionException
    }
}
```

Driver MUST:

* return valid connection
* return valid driver instance
* be healthy (`healthCheck()`)

If ANY fails → RepositoryConnectionException.

---

# 🧪 Fake vs Real Resiliency

Fake drivers MUST produce **identical** behavior:

| Operation    | Real Failure    | Fake Equivalent             |
|--------------|-----------------|-----------------------------|
| timeout      | network timeout | latency simulator           |
| driver error | PDOException    | ErrorSimulator::fail()      |
| disconnect   | broken pipe     | FakeAdapter::disconnect()   |
| data error   | invalid row     | malformed FakeStorage entry |

---

# 📝 Resiliency Logging

MUST log:

* connection errors
* retries
* timeout exceeded
* invalid driver instance
* adapter mismatch
* failed health checks
* exceptions thrown by simulation

Log fields:

```
operation
driver_type
repository
adapter_class
retry_count
duration_ms
exception_class
exception_message
```

---

# 📊 Error Classification Table

| Class                         | Meaning                           |
|-------------------------------|-----------------------------------|
| RepositoryConnectionException | connection or reconnection failed |
| RepositoryDriverException     | driver type or protocol error     |
| RepositoryTimeoutException    | operation exceeded timeout        |
| RepositoryDataException       | inconsistent or invalid data      |

---

# 📜 Adapter & Driver Contracts

AdapterInterface MUST guarantee:

* connect() sets valid driver
* healthCheck() returns true/false
* getDriver() NEVER returns invalid type
* MUST NOT return mixed
* MUST NOT return partial driver

FakeAdapter MUST enforce:

* simulation of broken drivers
* simulation of health failure
* simulation of latency
* simulation of data corruption

---

# 🔗 Integration Scenarios

Repository MUST handle:

* lost connection during query
* timeout while waiting
* fake error injection
* failover from bad DBAL connection
* retry on “server gone away”
* Mongo collection missing
* Redis pipeline failure

ALL must end in typed Maatify exceptions.

---

# 🧪 CI & Test Behavior

### Fake Tests MUST cover:

* timeouts
* disconnections
* invalid drivers
* retries
* error simulation
* latency simulation

### Real Tests MUST cover:

* actual driver exceptions
* real connection drop behavior
* real timeout behavior
* actual DBAL/Redis/Mongo errors

---

# 📘 Examples

Examples showing retry + timeout + driver error are included in `/examples/resiliency/`.

---

# 🟪 Summary

Resiliency layer MUST:

* normalize all driver errors
* guarantee predictable behavior
* integrate with fake simulator
* validate drivers before use
* provide retry + backoff rules
* ensure deterministic exceptions
* log every failure flow

This is an authoritative file
and MUST NOT be modified except through roadmap updates.
