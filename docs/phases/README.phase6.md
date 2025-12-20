# Phase 6 — Limit / Offset Safety & Normalization

**Project:** maatify/data-repository  
**Phase:** Phase 6  
**Status:** APPROVED — Capability Formalization (Non-Breaking)  
**Version Scope:** v1.x only  

---

## 🎯 Purpose

Phase 6 exists to **formalize and standardize limit/offset handling**
across repository pagination **without modifying any contractual API**.

This phase focuses on **safety, normalization, and documentation**, not on
introducing new guarantees or expanding repository contracts.

---

## 🧭 Architectural Context

Phase 6 is governed by the following locked decisions:

- **Phase 5A — Repository Contract Lock**
- **ADR-001** — Library Scope & Non-Goals
- **ADR-014** — Backward Compatibility
- **ADR-015** — Release Process & Governance

As a result:

- `RepositoryInterface` is **LOCKED**
- Pagination methods are **non-contractual convenience utilities**
- No deterministic ordering guarantees exist in v1.x

---

## 📦 Existing Capabilities (Observed)

The codebase already contains a fully implemented and reusable utility:

### `LimitOffsetValidator`
Location:
```

src/Generic/Support/LimitOffsetValidator.php

```

Capabilities:
- Enforces upper bounds for `limit` and `offset`
- Normalizes values to safe ranges
- Supports configurable limits via `LimitOffsetConfig`
- Standalone and adapter-agnostic

This phase **formalizes** its usage and expectations.

---

## ✅ Scope — What Phase 6 Provides

Phase 6 MAY include:

### 1. Standardized Limit / Offset Validation
- Formal recognition of `LimitOffsetValidator`
- Documentation of default limits and configuration options

### 2. Safe Integration in Convenience Methods
- Applying validation inside:
  - `paginate(...)`
  - `paginateBy(...)`
- **No signature changes**
- **No interface expansion**

### 3. Runtime Safety
- Preventing unbounded offsets or limits
- Ensuring behavior is deterministic *in execution*, not in ordering

---

## 🚫 Out of Scope (Explicitly Forbidden)

Phase 6 MUST NOT include:

- ❌ Changes to `RepositoryInterface`
- ❌ Adding `limit()` / `offset()` methods
- ❌ Adding parameters to contractual methods (`findAll`, `findBy`)
- ❌ Query builder or fluent pagination APIs
- ❌ Ordering guarantees or assumptions
- ❌ Any behavior that depends on Phase 5 execution

---

## ⚠️ Pagination Semantics Warning

### ⚠️ IMPORTANT NOTICE

Pagination in v1.x is **BEST-EFFORT ONLY**.

Reasons:
- Phase 5 (Ordering & Interface Segregation) is **architecturally deferred**
- Underlying drivers may return records in **undefined order**
- Redis pagination relies on SCAN-based iteration

As a result:
- Page boundaries are **not guaranteed**
- Page 2 may not be a stable continuation of Page 1
- Deterministic pagination is NOT provided in v1.x

This limitation MUST be clearly documented and communicated.

---

## 🧪 Redis Considerations

Redis defines the **lowest common denominator**.

Rules:
- Large offsets are potentially expensive
- Safety limits MUST prevent excessive SCAN operations
- Failure must be **graceful**, never fatal

Consumers are encouraged to:
- Use small limits
- Avoid deep offsets
- Treat Redis pagination as approximate

---

## 🔒 Backward Compatibility

Phase 6 introduces:
- **No breaking changes**
- **No new contracts**
- **No API guarantees**

All existing behavior remains valid under **ADR-014**.

---

## 🏁 Completion Criteria

Phase 6 is considered complete when:

- Limit/offset behavior is documented
- Validation is consistently applied where applicable
- Safety limits are enforced
- No contractual API is modified

---

## 🧾 Status Summary

| Aspect                   | Status         |
|--------------------------|----------------|
| Contract Changes         | ❌ None         |
| Breaking Changes         | ❌ None         |
| Capability Added         | ✅ Yes          |
| Deterministic Pagination | ❌ Not provided |
| v1.x Compatibility       | ✅ Guaranteed   |

---

**Phase 6 is a CAPABILITY PHASE, not a FEATURE GUARANTEE PHASE.**  
Future ordering guarantees require Phase 5 execution in v2.0.

---