# 🧠 WORK_SYSTEM — Execution & Governance Handbook

**Project:** `maatify/data-repository`

**Purpose:**
This document defines **how work is executed, reviewed, locked, and resumed** on this project.
It is the operational handbook that binds **ADRs, Roadmap Phases, AI execution, and human review** into a single deterministic system.

---

## 🎯 Core Objectives

The WORK_SYSTEM exists to:

* Eliminate ambiguity during development
* Prevent architectural drift
* Minimize context required when resuming work
* Enable deterministic AI-assisted execution
* Enforce ADRs as binding architectural law

---

## 🏛 Architectural Authority Hierarchy

Decisions are resolved strictly in the following order:

1. **Architecture Decision Records (ADRs)** — Highest authority
2. **roadmap.json** — Phase sequencing & scope
3. **Phase Audit Files** — Compliance verification
4. **Source Code** — Must conform to all above

> Code never overrules ADRs.
> Documentation never overrules ADRs.

---

## 📜 ADR Governance Rules

* ADRs are **binding**, not advisory
* ADRs apply globally unless explicitly scoped
* Any deviation requires:

    * A new ADR
    * Explicit rejection or amendment of the old ADR
    * MAJOR version bump

---

## 🗺 Phase-Based Execution Model

Development is strictly **phase-based**.

Each phase:

* Has a clearly defined scope
* References explicit ADRs
* Produces mandatory artifacts
* Is locked after audit

No overlapping or partial phase execution is allowed.

---

## 📦 Mandatory Artifacts Per Phase

Every phase MUST produce:

* `phase-output.json`
* `api-map.json`
* `README.phaseX.md`

Optional but recommended:

* `docs/audit/PHASEX_AUDIT.md`

A phase without artifacts is considered **non-existent**.

---

## 🔍 Phase Audit & Locking

Before a phase is considered complete:

1. Scope is validated against roadmap.json
2. All referenced ADRs are reloaded
3. Artifacts are reviewed for compliance
4. An audit verdict is issued

Once locked:

* Phase artifacts become immutable
* Changes require ADR + MAJOR bump

---

## 🤖 AI-Assisted Execution Rules

When AI systems are used:

* They must operate in **STRICT MODE**
* They must be provided only:

    * ADRs
    * roadmap.json
    * phase artifacts
* Conversation history must not be relied upon

AI is treated as an **execution engine**, not a decision-maker.

---

## 🚦 Change Control

Allowed without ADR:

* Bug fixes within phase scope
* Documentation clarification
* Internal refactoring with identical behavior

Disallowed without ADR:

* Scope expansion
* Behavioral changes
* API changes
* Architectural reinterpretation

---

## 🔒 Phase Lock Inheritance

Locked phases:

* Serve as immutable foundations
* Cannot be retroactively modified
* Must be respected by all future phases

Violating a locked phase invalidates downstream work.

---

## 🧭 Phase 1 Lock Reference

Phase 1 is locked and certified by:

* `docs/audit/PHASE1_AUDIT.md`

This lock is binding for all subsequent phases.

---

## 🧭 Phase 2 Lock Reference

Phase 2 is locked and certified by:

* `docs/audit/PHASE2_AUDIT.md`

This lock is binding for all subsequent phases and constrains all repository behavior built on top of the base layer.

---

## 🧭 Phase 3 Lock Reference

Phase 3 is locked and certified by:

* `docs/audit/PHASE3_AUDIT.md`

This lock formally defines **Phase 3 — CRUD Layer** as a **minimal, deterministic CRUD contract only**.

### Binding Constraints

The following are **explicitly forbidden** within Phase 3 and are treated as out-of-scope:

- Pagination of any kind
- Pagination DTOs
- Count semantics standardization
- Advanced filtering or sorting
- Hydration lifecycle logic
- Redis relational or performance assumptions
- SQL identifier quoting rules
- MongoDB ObjectId casting rules
- Interface segregation (read/write split)

Any existing implementation providing the above behaviors is classified as **legacy or future-phase behavior** and does **not** alter the Phase 3 architectural contract.

### Enforcement

- Phase 3 is **architecturally immutable**
- Any modification to Phase 3 scope or guarantees requires:
  - A new ADR
  - A **MAJOR** version bump

All subsequent phases MUST respect this lock.

---

## 🧭 Phase 4 Lock Reference

Phase 4 is locked and certified by:

* `docs/audit/PHASE4_AUDIT.md`

This lock formally defines **Phase 4 — Redis Safety Enforcement** as a
**runtime safety and protection phase only**.

### Binding Constraints

Phase 4 is strictly limited to:

- Runtime safety guards for Redis operations
- SCAN-only iteration enforcement
- Hard limits on:
  - Scan iterations
  - Total scanned keys
- Fail-fast Redis-specific exceptions

The following are **explicitly forbidden** within Phase 4:

- Pagination logic
- Filtering or sorting behavior
- Hydration lifecycle logic
- Redis performance optimization
- Query planning or indexing
- SQL or MongoDB behavior changes
- Public API or interface changes
- Legacy behavior refactoring

Any Redis-related behavior outside these constraints is classified as
**future-phase responsibility**.

### Fake vs Real Driver Rule

- Safety guards apply **only** to real Redis drivers
- Fake/test drivers are explicitly excluded
- Test semantics must remain unlimited and deterministic

### Enforcement

- Phase 4 is **architecturally immutable**
- Any modification to Redis safety behavior requires:
  - A new ADR
  - A **MAJOR** version bump

All subsequent phases MUST respect this lock.

---

## Phase 5A — Repository Contract Lock (MANDATORY)

### Status
LOCKED — No Code Changes

### Purpose
Phase 5A exists to **freeze and protect the repository contract** before any interface refactoring.
This phase explicitly prevents implicit contract expansion and guards backward compatibility.

### Core Rule
> **Only methods declared in `RepositoryInterface` are considered CONTRACTUAL.**  
> Any method implemented in concrete repositories but not declared in the interface is classified as **Convenience / Implementation Detail**.

### Contractual Methods (v1.x)
The following methods are the ONLY guaranteed API surface in v1.x:

- `find(id)`
- `findBy(filters)`
- `findAll()`
- `insert(data)`
- `update(id, data)`
- `delete(id)`
- `setAdapter(adapter)`

### Non-Contractual (Convenience) Methods
The following methods MAY exist in concrete repositories but are NOT part of the public contract:

- `findOneBy(...)`
- `count(...)`
- `paginate(...)`
- `paginateBy(...)`

These methods:
- MUST NOT be relied upon in type-hinted contracts
- MUST NOT be added to interfaces in v1.x
- MAY vary by driver capability (especially Redis)

### Redis Capability Baseline
Redis defines the **lowest common denominator** for repository behavior.

Rules:
- Redis limitations dictate contract design
- No repository may throw "Not Supported" for contractual methods
- Advanced behaviors must be optional and non-contractual

### Static Analysis & Quality Gates
All phases MUST comply with:
- PHPStan **Level Max**
- `declare(strict_types=1)`
- Deterministic behavior
- No runtime capability checks leaking into contracts

### Forward Compatibility
- Interface segregation (Read/Write repositories) is **explicitly deferred to v2.0**
- Any interface expansion in v1.x is considered a breaking change and forbidden

### Authority
This phase is governed by:
- ADR-001 (Scope Lock)
- ADR-003 (Capability Safety)
- ADR-014 (Backward Compatibility)
- ADR-015 (Governance)
- ADR-016 (Repository Contract Boundary)

---

> Phase 5 (Interface Segregation – Read/Write Repositories)  
> is explicitly deferred to **v2.0** and MUST NOT be implemented in v1.x.

---

## 🧭 Phase 6 Lock Reference

### Phase 6 — Limit / Offset Safety

**Status:** ARCHITECTURALLY LOCKED

Phase 6 formalizes **limit/offset safety enforcement** for pagination behavior
without modifying any contractual API.

This phase is classified as a **Capability Formalization Phase**, not a feature
or guarantee phase.

---

### Scope Guarantees

Phase 6 guarantees that:

- Limit / offset validation is applied **only** in:
  - `paginate(...)`
  - `paginateBy(...)`
- Validation is **fail-fast**
- Validation occurs **before data access**
- No method signatures were changed
- No interfaces were expanded
- No new guarantees were introduced

---

### Explicit Non-Guarantees

Phase 6 does **NOT** provide:

- Deterministic pagination ordering
- Stable page boundaries
- Ordering semantics across adapters
- Any new contractual API surface

Pagination remains **best-effort only** in v1.x.

---

### Governing Authority

Phase 6 is governed by:

- Phase 5A — Repository Contract Lock
- ADR-001 — Library Scope & Non-Goals
- ADR-014 — Backward Compatibility
- ADR-015 — Release Process & Governance

---

### Audit Reference

Phase 6 is certified and locked by:

- `docs/audit/PHASE6_AUDIT.md`

This lock is **binding** for all future phases.

Any change affecting Phase 6 behavior requires:
- A new ADR **or**
- A MAJOR version bump

---

## 🧭 Phase 7 Lock Reference

### Phase 7 — MongoDB Explicit Behavior

**Status:** ARCHITECTURALLY LOCKED

Phase 7 defines and locks MongoDB ObjectId casting behavior with an explicit-only policy to ensure predictability and safety.

### Binding Guarantees

- Casting is allowed **ONLY** in `find(id)`
- 24-character hexadecimal strings are cast **ONLY** in `find(id)`
- `findBy`, `paginate`, and filter-based queries **NEVER** perform implicit casting
- Explicit `new MongoDB\BSON\ObjectId(...)` is required in filters
- Behavior is identical across **real and fake adapters**

### Explicit Non-Guarantees

- No heuristic or content-based ObjectId detection in filters
- No implicit casting for `_id` fields passed via filter arrays
- No adapter-specific behavior divergence

### Governing Authority

This phase is governed by:
- **ADR-005** — MongoDB ObjectId Casting Rules & Safety
- **ADR-014** — Backward Compatibility & Deprecation Policy
- **ADR-015** — Release Process & Governance

### Audit Reference

- `docs/audit/PHASE7_AUDIT.md`  
  (Initial audit + Post-Remediation Verification)

### Enforcement

Any modification to Phase 7 behavior requires:
- A **new ADR**, **or**
- A **MAJOR version bump**

This lock is binding for all subsequent phases.

---

## 🧭 Phase 8 Lock Reference

### Phase 8 — Pagination & Count Semantics

**Status:** ARCHITECTURALLY LOCKED

Phase 8 formalizes and locks the semantic contract for pagination and count behavior
across all repository backends, with explicit Redis safety constraints.

This phase is classified as a **Contract Clarification Phase**.
No new features, optimizations, or behavioral changes were introduced.

---

### Binding Guarantees

- All pagination methods return a `PaginationResultDTO`
- Pagination metadata (`total`, `page`, `perPage`, `pages`) is deterministic and consistent
- `pages` is always calculated as `ceil(total / perPage)`
- `LimitOffsetValidator` is enforced for all pagination paths
- Behavior is identical across **Fake and Real** adapters

---

### Count Semantics

#### MySQL & MongoDB
- `count()` returns total records
- `count(array $filters)` returns filtered record count

#### Redis
- `count()` returns total keys matching repository prefix
- `count(array $filters)` is **NOT SUPPORTED** and MUST throw `RepositoryException`
- No standalone filtered counts are allowed in Redis

---

### Redis Pagination Clarification

- `paginateBy(array $filters)` is supported in Redis
- `PaginationResultDTO.total` is available during pagination
- This `total` value is a **pagination side-effect**, not a general contract guarantee
- The presence of `total` does NOT imply support for `count(array $filters)`

---

### Explicit Non-Guarantees

- No implicit or heuristic filtered counts in Redis
- No partial, approximate, or best-effort counts
- No Redis query-engine behavior
- No relaxation of safety limits enforced by `RedisOps`

---

### Governing Authority

This phase is governed by:
- **ADR-010** — Pagination Result Contract
- **ADR-011** — Pagination & Count Semantics
- **ADR-006** — Redis Safety Constraints
- **ADR-014** — Backward Compatibility Policy
- **ADR-015** — Release & Lock Governance

---

### Audit Reference

- `docs/audit/PHASE8_AUDIT.md`
- `docs/phases/README.phase8.md`

---

### Enforcement

Any modification to Phase 8 semantics requires:
- A **new ADR**, or
- A **MAJOR version bump**

This lock is binding for all subsequent phases.

---

## 🧭 Phase 9 Lock Reference

### Phase 9 — Generic Ops Integration

**Status:** ARCHITECTURALLY LOCKED

Phase 9 confirms and locks the behavior of low-level Ops helpers used by
Generic Repositories.

This phase is classified as a **Stabilization & Isolation Phase**.
No feature additions or behavioral changes are permitted.

---

### Scope Guarantees

Phase 9 guarantees that:

- Ops classes are **execution helpers only**
- No business logic, pagination, or hydration logic exists in Ops
- Ops classes remain isolated from:
  - Repositories
  - Adapters
  - DTOs
- Redis safety rules are enforced via bounded SCAN operations
- Fail-fast behavior is deterministic and explicit
- Fake drivers exhibit **identical semantics** to real drivers

---

### Locked Components

- `MysqlOps`
- `MongoOps`
- `RedisOps`
- `RedisGuard`
- `RedisSafetyConfig`

---

### Explicit Non-Goals

- Performance optimizations
- Feature expansion
- Pagination or filtering logic
- Adapter abstraction changes

Any change to this phase requires:
- A new ADR
- A new phase
- A full audit cycle

---

## 🏁 Final Principle

> Stability over speed.
> Safety over convenience.
> Explicit over implicit.

This WORK_SYSTEM is itself **architecturally binding**.

Any modification to this document requires:

* Architectural review
* Explicit approval
* Documentation of rationale

---

**Maintained by:** Maatify Architecture

**Status:** ACTIVE
