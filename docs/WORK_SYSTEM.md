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
