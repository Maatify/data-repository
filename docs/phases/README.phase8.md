# Phase 8 — Pagination & Count Semantics

## Phase Status
**ARCHITECTURALLY LOCKED**

Phase 8 formalizes and locks the semantic contract for pagination and count behavior
across all supported backends, with explicit handling for Redis safety constraints.

This phase is a **contract clarification phase**, not a feature or optimization phase.

---

## Scope

Phase 8 governs the behavior and guarantees of:

- `paginate()`
- `paginateBy(array $filters)`
- `count()`
- `PaginationResultDTO`

Across all repository implementations:
- MySQL
- MongoDB
- Redis
- Fake vs Real adapters

---

## Governing ADRs

- **ADR-010** — Pagination Result Contract
- **ADR-011** — Pagination & Count Semantics
- **ADR-006** — Redis Safety Constraints
- **ADR-014** — Backward Compatibility
- **ADR-015** — Release & Lock Policy

These ADRs are binding for all behaviors defined in this phase.

---

## PaginationResultDTO Contract

All pagination methods MUST return a `PaginationResultDTO` instance.

### Guaranteed Structure

The DTO always contains:

- `items` — Result items for the current page
- `pagination.total` — Total number of matching items
- `pagination.page` — Current page number
- `pagination.perPage` — Items per page
- `pagination.pages` — Total pages (`ceil(total / perPage)`)

### Determinism

- Pagination metadata is computed deterministically
- Page count calculation is centralized and consistent across all drivers
- No backend-specific variation is allowed in DTO structure

---

## `count()` Semantics

### General Rule

`count()` reflects the total number of items **matching the repository scope**.

### Relational & Document Backends (MySQL, MongoDB)

- `count()`  
  Returns the total number of records.

- `count(array $filters)`  
  Returns the number of records matching the provided filters.

This behavior is consistent with query-capable backends.

---

## Redis-Specific Semantics

Redis repositories operate under **explicit safety-first constraints**.

Redis is treated as a **key-value store**, not a query engine.

---

### Redis `count()` Behavior

| Operation | Behavior |
|---------|----------|
| `count()` | ✅ Supported — returns total keys matching repository prefix |
| `count(array $filters)` | ❌ Not supported — throws `RepositoryException` |

#### Rationale

Supporting filtered counts in Redis would require:

- Fetching all matching keys
- Deserializing all values
- Applying in-memory filtering

This is an unbounded, high-cost operation and is therefore **explicitly forbidden**.

Redis repositories **fail fast** when filtered counts are requested.

This behavior is intentional, enforced, and non-negotiable.

---

## Pagination in Redis

### `paginateBy(array $filters)`

Redis **does support pagination with filters**, with strict safeguards:

- Pagination necessarily fetches and filters the dataset in memory
- Safety limits are enforced by `RedisOps`
- If safety limits are exceeded, the operation fails fast

### `PaginationResultDTO.total` in Redis

During pagination, `PaginationResultDTO.total` **will be present and accurate**.

#### Important Clarification

The presence of `total` during Redis pagination is a **side-effect** of the mandatory
in-memory filtering required to perform pagination.

It **does NOT imply** that `count(array $filters)` is supported as a standalone operation.

---

## Contract Summary

| Operation                   | MySQL | MongoDB | Redis                           |
|-----------------------------|-------|---------|---------------------------------|
| `count()`                   | ✅     | ✅       | ✅                               |
| `count($filters)`           | ✅     | ✅       | ❌ (throws)                      |
| `paginate()`                | ✅     | ✅       | ✅                               |
| `paginateBy($filters)`      | ✅     | ✅       | ✅                               |
| `PaginationResultDTO.total` | ✅     | ✅       | ✅ (pagination-only side-effect) |

---

## Safety Guarantees

- Redis repositories will **never** expose standalone filtered counts
- No implicit, heuristic, or approximate counts are allowed
- Safety limits enforced by `RedisOps` are always respected
- Fake and Real Redis adapters share identical semantics

---

## Non-Goals

Phase 8 explicitly does NOT introduce:

- New pagination features
- Redis query optimizations
- Approximate or partial counts
- Behavioral changes in existing APIs

---

## Audit Reference

- `docs/audit/PHASE8_AUDIT.md`

This document includes:
- ADR compliance matrix
- Safety analysis
- Post-documentation verification
- Final audit verdict

---

## Enforcement

Any modification to Phase 8 semantics requires:

- A **new ADR**, or
- A **MAJOR version bump**

This lock is binding for all subsequent phases.
