# Phase 6 — Limit / Offset Safety (Pagination Only)

**Project:** maatify/data-repository
**Phase:** Phase 6
**Status:** COMPLETED — Capability Safety (Non-Breaking)
**Version Scope:** v1.x

---

## 🎯 Purpose

Phase 6 exists to **enforce runtime safety validation** for pagination parameters
(`limit` and `offset`) **without modifying any contractual API**.

This phase is strictly defensive and mechanical.
It does **not** introduce normalization, configuration, or new guarantees.

---

## 🧭 Architectural Context

Phase 6 is governed by:

* **Phase 5A — Repository Contract Lock**
* **ADR-001** — Scope Lock
* **ADR-014** — Backward Compatibility
* **ADR-015** — Governance

As a result:

* `RepositoryInterface` is **LOCKED**
* Pagination methods are **non-contractual convenience methods**
* No interface expansion is allowed in v1.x

---

## 📦 Capability Used

### `LimitOffsetValidator`

Location:

```
src/Generic/Support/LimitOffsetValidator.php
```

Role:

* Validates `limit` and `offset`
* Enforces hard safety bounds
* Throws `RepositoryException` on invalid input

This class is used **as-is**, without extension or configuration.

---

## ✅ Scope — What Phase 6 Provides

### 1. Pagination Safety Validation

Validation is applied **ONLY** in:

* `paginate(...)`
* `paginateBy(...)`

Validation guarantees:

* `limit >= 1`
* `offset >= 0`
* Upper bounds enforced

### 2. Driver Consistency

Validation behavior is identical across:

* MySQL
* MongoDB
* Redis

### 3. Deterministic Execution (Not Ordering)

* Invalid pagination inputs fail fast
* No undefined runtime behavior
* Execution is safe, not semantically ordered

---

## 🚫 Explicitly Out of Scope

Phase 6 **DOES NOT** include:

* ❌ Normalization or clamping
* ❌ `LimitOffsetConfig`
* ❌ Runtime configuration
* ❌ Interface changes
* ❌ Additional method parameters
* ❌ Ordering guarantees
* ❌ Query builder logic

---

## ⚠️ Pagination Semantics Warning

> Pagination in v1.x is **BEST-EFFORT ONLY**.

Reasons:

* No deterministic ordering exists
* Phase 5 (Ordering) is deferred
* Redis relies on SCAN iteration

As a result:

* Page boundaries are not stable
* Page 2 is not guaranteed to follow Page 1
* This is expected and documented behavior

---

## 🧪 Redis Considerations

* Large offsets may be expensive
* Safety validation prevents crashes
* Performance guarantees are NOT provided

Redis defines the **lowest common denominator**.

---

## 🔒 Backward Compatibility

* No breaking changes
* No contract expansion
* Fully compliant with **ADR-014**

Safe for all v1.x consumers.

---

## 🏁 Completion Criteria

Phase 6 is complete because:

* Validation is consistently applied
* No contracts were touched
* Safety is enforced
* Scope is respected

---

## 🧾 Status Summary

| Aspect              | Status |
|---------------------|--------|
| Contract Changes    | ❌ None |
| Normalization       | ❌ No   |
| Configuration       | ❌ No   |
| Validation Added    | ✅ Yes  |
| Pagination Ordering | ❌ No   |
| v1.x Compatibility  | ✅ Yes  |

---

**Phase 6 is a SAFETY PHASE, not a PAGINATION FEATURE PHASE.**

---
