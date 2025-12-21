# Phase 9 — Generic Ops Integration

## Status
✅ **COMPLETED & LOCKED**

Phase 9 finalizes and locks the **Generic Ops layer**, which provides low-level,
driver-facing execution helpers for MySQL, MongoDB, and Redis.

This phase is a **Stabilization & Isolation Phase** — no new features were introduced,
and no source code changes were required.

---

## Purpose

The purpose of Phase 9 is to:

- Isolate **execution concerns** from repository logic
- Enforce **strict architectural boundaries**
- Guarantee **deterministic behavior** across real and fake drivers
- Harden **Redis safety rules** using bounded SCAN operations
- Ensure Ops helpers remain **infrastructure-only**

---

## In Scope

The following components were audited and locked:

- `src/Generic/Support/MysqlOps.php`
- `src/Generic/Support/MongoOps.php`
- `src/Generic/Support/RedisOps.php`
- `src/Generic/Support/Safety/RedisGuard.php`
- `src/Generic/Support/Safety/RedisSafetyConfig.php`

---

## Responsibilities (Locked)

### MysqlOps
- Normalizes MySQL driver behavior
- Handles edge cases such as:
  - `lastInsertId()` overflow on 64-bit boundaries
- Provides Fake-driver parity via `method_exists` checks
- **Does NOT**:
  - Build queries
  - Apply filters
  - Perform pagination
  - Hydrate results

---

### MongoOps
- Handles BSON-related operations only
- Iterates cursors and normalizes documents
- Accepts `object` drivers to support Fake collections
- Supports mocked documents via `getArrayCopy()`
- **Does NOT**:
  - Apply filters
  - Perform pagination
  - Hydrate to entities or DTOs

---

### RedisOps
- Standardizes Redis operations across:
  - `ext-redis`
  - `predis/predis`
  - Fake Redis adapters
- Normalizes return values for `GET`, `SET`, `DEL`
- Implements **bounded SCAN-based key iteration**
- Enforces safety via `RedisGuard`

#### Redis Safety Guarantees
- SCAN iteration is **bounded by both**:
  - Maximum key count
  - Maximum iteration count
- Empty SCAN batches still count toward iteration limits
- Violations trigger **immediate fail-fast exceptions**
- Fake drivers are validated against the **same limits** as real drivers

---

## Architectural Boundaries

Phase 9 strictly enforces the following boundaries:

- Ops classes:
  - Depend only on **raw drivers** (PDO, Redis, MongoDB\Collection)
  - Do NOT depend on:
    - Repositories
    - Adapters
    - DTOs
    - Pagination logic
    - Hydration logic
- Ops helpers are **stateless** and deterministic
- No implicit behavior or heuristic logic exists

---

## ADR Compliance

| ADR | Requirement | Status |
|----|------------|--------|
| ADR-001 | Architecture Boundaries | ✅ PASS |
| ADR-007 | Fail-Fast & Determinism | ✅ PASS |
| ADR-014 | No Implicit Behavior | ✅ PASS |
| ADR-015 | Fake vs Real Parity | ✅ PASS |

---

## Audit Outcome

**Audit Result:** ✅ PASS  
**Code Changes:** ❌ None  
**Scope Modified:** `docs/**` only

The Ops layer was found to be:

- Correctly scoped
- Deterministic
- Safe under Redis constraints
- Fully symmetric between Fake and Real drivers

---

## Lock Declaration

Phase 9 is **ARCHITECTURALLY LOCKED**.

The following rules now apply:

- ❌ No functional changes allowed
- ❌ No behavioral extensions allowed
- ❌ No performance tuning allowed
- ❌ No feature additions allowed

Any change to the Ops layer requires:

1. A new ADR
2. A new phase
3. A full audit cycle

---

## Relationship to Other Phases

- Builds on:
  - Phase 6 — Limits & Offsets
  - Phase 7 — Result Normalization
- Prepares groundwork for:
  - Phase 10 — Pagination Preparation
  - Phase 11+ — Hydration & DTO Pipelines

---

## Final Note

Phase 9 represents the **final stabilization point** of the infrastructure
execution layer. All higher-level features (pagination, hydration, DTO mapping)
build on top of this locked foundation.

No further work is expected in this phase.

---
