# 🧹 Cleanup & Resource Management Rules
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Define the mandatory cleanup rules for the repository layer to prevent memory leaks, stale state retention, broken driver references, and inconsistent fake/real behavior.

Cleanup applies to:
- PDO / DBAL connections
- MongoDB database & collection references
- Redis / Predis connections
- Hydration artifacts
- Pagination temporary structures
- Filters & normalization buffers
- Fake driver in-memory states
- Exceptions & error objects

---

# 📚 Table of Contents

1. [Cleanup Philosophy](#cleanup-philosophy)
2. [When Cleanup Happens](#when-cleanup-happens)
3. [Repository-Level Cleanup](#repository-level-cleanup)
4. [Driver-Level Cleanup](#driver-level-cleanup)
5. [Hydration Cleanup](#hydration-cleanup)
6. [Normalization & Filtering Cleanup](#normalization--filtering-cleanup)
7. [Pagination Cleanup](#pagination-cleanup)
8. [Exception Cleanup](#exception-cleanup)
9. [Fake Driver Cleanup](#fake-driver-cleanup)
10. [Memory Safety Rules](#memory-safety-rules)
11. [Cleanup Checklist](#cleanup-checklist)
12. [Summary](#summary)

---

# 🧠 Cleanup Philosophy

The Repository Layer MUST ALWAYS:

- Avoid long-lived memory
- Avoid state retention
- Avoid leftover references
- Avoid keeping temporary arrays after a method ends
- Ensure predictable results between calls
- Ensure Fake + Real behave identically

Repositories MUST remain fully stateless.

No repository method is allowed to keep any internal state  
between different calls.

---

# ⏱ When Cleanup Happens

Cleanup MUST happen:

### ✔ After every repository method call
(find / findBy / insert / update / delete / paginate / count)

### ✔ After driver-level execution
(clear local variables, result sets, and cursors)

### ✔ After hydration
(remove intermediate arrays)

### ✔ After normalization
(drop temporary buffers)

### ✔ After throwing RepositoryException
(clean local references before rethrow)

### ✔ Before returning output
(ensure temporary variables are unset)

---

# 🧱 Repository-Level Cleanup

The repository MUST:

- unset temporary `$rows` after hydration
- unset `$normalizedFilters`
- unset `$payload`
- unset `$normalizedPayload`
- unset `$sortOptions`
- unset `$paginationState`
- reset internal buffers used by filtering
- NEVER hold references to data fetched from driver

Forbidden:

❌ storing `$this->lastResult`  
❌ storing `$this->lastFilters`  
❌ caching database results inside repository

Repository MUST **not** act as a cache.

---

# 🎛 Driver-Level Cleanup

## ✔ PDO
- MUST close cursors after fetch
- MUST unset prepared statements
- MUST NOT reuse cursor across calls
- MUST ensure no lingering PDOStatement in object properties

## ✔ DBAL
- MUST release result sets
- MUST NOT hold onto query builders
- MUST NOT store DBAL connections internally

## ✔ MongoDB
- MUST release cursor after a query
- MUST NOT keep references to MongoDB\Collection after method ends

## ✔ Redis / Predis
- MUST NOT store pipeline objects
- MUST not keep multi/exec state
- MUST clear PHPOBJ references

---

# 🌿 Hydration Cleanup

Hydration layer MUST:

- drop temporary arrays
- drop mapping metadata if cached for this execution only
- avoid deep copying unnecessary structures
- avoid storing hydrated objects internally

Forbidden:

❌ keeping a map of hydrated objects ($this->hydrated[])  
❌ storing DTO relations in memory  
❌ incremental hydration caches

Allowed:

✔ static property metadata cached ONCE for all executions  
(not cleaned per method).

---

# 🧹 Normalization & Filtering Cleanup

Repository MUST:

- unset normalized filter arrays
- unset internal operator maps
- unset normalized payload arrays
- unset temporary buffers used during filter application
- ensure filters do NOT survive to next method
- ensure sorting config is cleared

---

# 📄 Pagination Cleanup

Pagination cleanup MUST ensure:

- clearing offset/limit temporary variables
- clearing local sorting arrays
- clearing temporary slices
- freeing in-memory slices BEFORE returning results
- ensuring large arrays are unset immediately

---

# ❗ Exception Cleanup

Before throwing a RepositoryException:

Repository MUST:

1. log the error
2. unset all large arrays
3. unset payload / filters / temporary hydration arrays
4. ensure no stale driver cursor remains open
5. ensure no temporary memory structure is kept alive

Exception MUST be thrown **after cleanup**, not before.

---

# 🧪 Fake Driver Cleanup

Fake drivers MUST also follow cleanup rules:

- drop in-memory operation buffers
- drop local sorting results
- drop lastFilter / lastPayload temporary structures
- apply snapshot rollback if enabled
- ensure next test starts clean

FakeEnvironment MUST:

- reset FakeStorageLayer between tests unless auto-reset is disabled
- clear Redis fake state
- clear Mongo fake collections
- reset simulation state (latency/error scenarios)

---

# 🧮 Memory Safety Rules

Repository MUST ensure:

- no circular references
- no storing closures internally
- no storing driver objects inside DTOs
- no storing hydrated objects in internal arrays
- no memory buildup across multiple operations

Large datasets MUST be freed ASAP.

---

![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# ☑ Cleanup Checklist

Before returning from ANY repository method:

- [ ] driver temporary result cleared
- [ ] filters cleared
- [ ] payload cleared
- [ ] normalization buffers cleared
- [ ] pagination temp arrays cleared
- [ ] sort arrays cleared
- [ ] temporary hydration arrays cleared
- [ ] error state cleared
- [ ] fake state synced & reset (if applicable)

---

# 🧩 Summary

Cleanup rules ensure:

- no memory leaks
- no stale state
- no inconsistent behavior
- no inter-test contamination
- deterministic repository behavior
- fake drivers behave exactly like real drivers
- repository remains fully stateless
- safe usage inside long-running workers

This document MUST NOT change  
except via roadmap-approved updates.
