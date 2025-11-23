![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🚀 Performance Rules
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Define strict performance rules for all repositories to ensure deterministic, efficient, and uniform behavior across all supported drivers.

Performance MUST be consistent across:

- MySQL (PDO)
- DBAL
- MongoDB
- Redis / Predis
- Fake drivers from maatify/data-fakes

---

# 📚 Table of Contents

1. [Performance Philosophy](#performance-philosophy)
2. [General Rules](#general-rules)
3. [Query Execution Performance](#query-execution-performance)
4. [Filtering & Normalization Performance](#filtering--normalization-performance)
5. [Hydration Performance](#hydration-performance)
6. [Pagination Performance](#pagination-performance)
7. [Driver-Specific Optimizations](#driver-specific-optimizations)
8. [Fake Driver Performance](#fake-driver-performance)
9. [Memory Usage](#memory-usage)
10. [Testing Performance](#testing-performance)
11. [Performance Budget](#performance-budget)
12. [Summary](#summary)

---

# 🧠 Performance Philosophy

Repositories MUST be:

- **Fast**
- **Deterministic**
- **Consistent** across drivers
- **Minimal overhead**
- **Predictable** even under heavy data loads
- **Zero-latency variability** in fake drivers

The repository layer MUST NOT introduce unnecessary computations.

---

# 🟦 General Rules

Repositories MUST:

1. Minimize data transformations
2. Minimize array copies
3. Avoid unnecessary sorting
4. Avoid unnecessary filtering
5. Avoid expensive operations on large datasets
6. Avoid reflection when possible
7. Reuse prepared statements (PDO) internally
8. Use indexed filtering strategies when possible (real DB drivers)
9. Rely on driver-level capabilities whenever safe

Repositories MUST NOT:

❌ fetch unbounded results  
❌ apply sorting twice  
❌ rehydrate existing hydrated data  
❌ duplicate filter operations  
❌ re-normalize already normalized payloads

---

# ⚡ Query Execution Performance

### ✔ PDO performance rules

- Use prepared statements
- Reuse statements when possible
- Avoid SELECT *
- Use LIMIT when possible
- Avoid fetching unnecessary columns
- Avoid row-by-row loops (prefer bulk fetch)

### ✔ DBAL performance rules

- Use `executeQuery` and `fetchAllAssociative`
- Avoid repeated query builder construction
- Avoid complex QueryBuilder expressions

### ✔ Mongo performance rules

- Use projection when possible
- Use indexed fields for filtering
- Avoid skip() with very large offsets
- Favor range queries over large IN arrays

### ✔ Redis/Predis performance

- Avoid KEYS and SCAN (forbidden)
- Avoid large multi-key pipelines
- Prefer hash structures for grouped values

---

# 🔍 Filtering & Normalization Performance

Filtering MUST:

- happen in PHP only when driver cannot filter (like Redis)
- be applied **once only**
- avoid unnecessary copies
- use optimized loops
- short-circuit when possible
- avoid regex operations

Normalization MUST:

- avoid repeated transformations
- avoid repeated utf8 sanitization
- cache expensive normalization steps where allowed

---

# 🧬 Hydration Performance

Hydration MUST:

- avoid reflection-heavy logic
- avoid dynamic property creation
- use pre-mapped constructor metadata
- avoid converting snake_case multiple times
- use lightweight array→object mapping

Forbidden:

❌ using ReflectionProperty inside hydration loops  
❌ creating new Normalizer instance per row  
❌ decoding/encoding json inside hydration  
❌ building objects recursively without limits

---

# 📄 Pagination Performance

Pagination MUST:

- apply LIMIT/OFFSET at driver level (PDO/DBAL/Mongo)
- fall back to PHP slicing for Redis
- avoid slicing before filtering
- avoid slicing before sorting

Sorting MUST:

- use safe built-in PHP functions
- avoid custom comparator functions unless required
- avoid repeated sorting

---

# 🔌 Driver-Specific Optimizations

| Driver               | Optimization                                                |
|----------------------|-------------------------------------------------------------|
| **PDO**              | limit columns, bulk fetch, reuse prepared statements        |
| **DBAL**             | associative fetch, avoid unnecessary query builder usage    |
| **MongoDB**          | projection, indexed filters, avoid large skip()             |
| **Redis**            | prefer HGETALL + PHP filtering, avoid large list operations |
| **Predis**           | minimize network round trips                                |
| **FakeStorageLayer** | use in-memory arrays efficiently                            |

---

# 🟣 Fake Driver Performance

Fake drivers MUST:

- behave instantly (zero latency unless latency simulation enabled)
- avoid unnecessary deep copies
- avoid large sorting operations
- avoid building large structures inside tests
- ensure deterministic results
- ensure O(n) filtering when possible

Fake performance MUST NOT degrade CI pipelines.

---

# 🧮 Memory Usage

Repositories MUST:

- avoid building massive temporary arrays
- avoid recursive hydration on huge datasets
- avoid loading entire Mongo collections unnecessarily
- enforce hard limits on pagination
- avoid cloning large structures

Large datasets MUST be streamed by drivers when possible  
(PDO + DBAL), but hydration still occurs post-fetch.

---

# 🧪 Testing Performance

### ✔ Fake tests MUST be extremely fast
- no artificial delays
- no real I/O
- no heavy loops

### ✔ Real tests MUST use small fixture datasets
Tests MUST NOT:

❌ load large DB tables  
❌ perform 1000+ operations per test  
❌ rely on real database latency

---

# 🔢 Performance Budget

Each repository operation MUST follow:

| Stage                 | Max Time              |
|-----------------------|-----------------------|
| validation            | < 0.1 ms              |
| normalization         | < 0.2 ms              |
| filtering             | < 0.3 ms              |
| pagination math       | < 0.05 ms             |
| hydration             | < 0.5 ms for 100 rows |
| fake driver execution | < 0.2 ms              |

These are soft budgets but MUST be used for performance regression tests later.

---

# 🧩 Summary

Repository performance MUST be:

- efficient
- deterministic
- minimal overhead
- optimized per driver
- identical between fake and real tests
- fully compliant with validation, filtering, hydration, and logging rules

This document MUST NOT change  
except via roadmap-approved updates.
