# ADR ↔ Roadmap Phase Mapping

This document defines the **explicit dependency mapping** between execution phases
(`roadmap.json v2.0.0`) and Architecture Decision Records (ADR-001 → ADR-015).

Its purpose is to:
- Eliminate ambiguity during execution
- Minimize required context when resuming work
- Enable deterministic AI-assisted development
- Prevent scope drift or architectural regression

---

## Global Constitutional ADRs (ALWAYS REQUIRED)

The following ADRs **MUST be present in every execution context**, regardless of phase:

- **ADR-001** — Project Scope & Non-Goals
- **ADR-014** — Backward Compatibility & Deprecation Policy
- **ADR-015** — Release Process & Governance

These ADRs define the immutable identity and rules of the project.

---

## Phase-by-Phase ADR Dependencies

### Phase 1 — Bootstrap & Governance (COMPLETED)

**Required ADRs:**
- ADR-001
- ADR-015

**Rationale:**
- Project identity and scope definition
- Governance, contribution, and release rules

---

### Phase 2 — Base Repository Layer (COMPLETED)

**Required ADRs:**
- ADR-001
- ADR-009 — RepositoryResolver Scope & Service Locator Risks

**Rationale:**
- Prevent Service Locator abuse
- Enforce adapter boundaries and responsibility separation

---

### Phase 3 — Generic CRUD (Minimal) (COMPLETED)

**Required ADRs:**
- ADR-001
- ADR-012 — Error & Exception Taxonomy

**Rationale:**
- CRUD behavior must remain minimal and predictable
- Error semantics must be unified and deterministic

---

### Phase 4 — Redis Safety Enforcement (NEXT EXECUTION PHASE)

**Required ADRs:**
- ADR-002 — Redis Behavior Model
- ADR-006 — Redis Safety Limits & Runtime Guards

**Rationale:**
- Redis is treated strictly as a Key-Value store
- SCAN-only behavior
- Hard safety limits and fail-fast enforcement
- Runtime protection against OOM and blocking operations

---

### Phase 5 — Interface Segregation (Read / Write)

**Required ADRs:**
- ADR-003 — Interface Segregation & Read/Write Contracts
- ADR-014 — Backward Compatibility Policy

**Rationale:**
- Enforce ISP (Interface Segregation Principle)
- Prevent unsupported method implementations
- Ensure non-breaking evolution in v1.x

---

### Phase 6 — SQL Identifier Quoting Consistency

**Required ADRs:**
- ADR-004 — SQL Identifier Quoting Consistency

**Rationale:**
- Prevent reserved keyword failures
- Enforce uniform quoting across read/write operations
- Eliminate SQL behavior inconsistencies

---

### Phase 7 — MongoDB Explicit Behavior

**Required ADRs:**
- ADR-005 — MongoDB ObjectId Casting Rules

**Rationale:**
- Remove implicit magic casting
- Make ObjectId handling explicit and predictable
- Align MongoOps usage

---

### Phase 8 — Pagination & Count Semantics

**Required ADRs:**
- ADR-010 — Pagination DTO & Result Guarantees
- ADR-011 — Count Semantics & Consistency

**Rationale:**
- Enforce DTO-based pagination output
- Prevent leaky abstractions across adapters
- Standardize count behavior

---

### Phase 9 — Hydration Lifecycle Enforcement

**Required ADRs:**
- ADR-007 — Hydration Lifecycle Contract

**Rationale:**
- Preserve the 5-stage hydration pipeline:
  Prepare → Cast → Map → Validate → Complete
- Hydration behavior is a locked identity feature

---

### Phase 10 — Error, Logging & Observability Boundaries

**Required ADRs:**
- ADR-012 — Error & Exception Taxonomy
- ADR-013 — Logging & Observability Boundaries

**Rationale:**
- Consistent exception behavior
- Prevent sensitive data leakage
- Maintain infrastructure-only logging responsibilities

---

### Phase 11 — Fake vs Real Adapter Parity

**Required ADRs:**
- ADR-008 — Fake Adapters & Testing Guarantees

**Rationale:**
- Ensure FakeAdapters behave identically to RealAdapters
- Deterministic testing
- Zero semantic drift between environments

---

### Phase 12 — Documentation & Philosophy

**Required ADRs:**
- ADR-001 — Scope & Non-Goals
- ADR-002 — Redis Behavior
- ADR-006 — Redis Safety Limits
- ADR-007 — Hydration Lifecycle Contract

**Rationale:**
- Educate consumers on intentional constraints
- Prevent misuse and incorrect assumptions
- Make philosophy explicit and visible

---

### Phase 13 — Release & Stability Lock

**Required ADRs:**
- ADR-014 — Backward Compatibility & Deprecation
- ADR-015 — Release Process & Governance

**Rationale:**
- Final stability verification
- Version tagging and changelog finalization
- Packagist publishing rules

---

## ⚠️ Governance Clarification — Hydration Implementation Drift (Historical)

### Context

During execution, a controlled implementation drift occurred between
**Phase 11 (Hydration Contracts)** and **Phase 12 (Documentation & Philosophy)**.

Specifically, the following hydration-related source files were introduced
*before* formal entry into Phase 13 (Release & Stability Lock):

- `src/Hydration/BaseHydrator.php`
- `src/Hydration/AutoCaster.php`
- `src/Hydration/MappingProfile.php`
- `src/Generic/Support/RepositoryHydrationTrait.php`

These files implement the hydration lifecycle defined in **ADR-007** and are
architecturally valid, but their presence violates the strict interpretation
of **Phase 12**, which is constitutionally defined as **Documentation & Philosophy only (No Code)**.

This clarification is formally referenced by:
- docs/audit/PHASE12_AUDIT.md (Audit Amendment section)

---

### Governance Resolution

To restore constitutional integrity **without destroying valid work or rewriting history**,
the following governance resolution is LOCKED:

1. **Phase Definitions in this document remain unchanged**
    - Phase 12 remains: *Documentation & Philosophy (No Code)*
    - Phase 13 remains: *Release & Stability Lock*

2. The hydration implementation code listed above is formally re-classified as:
   **"Pre-Phase 13 Hydration Implementation (Executed Early)"**

3. This re-classification is:
    - A **documentation correction**
    - **NOT** a behavioral or architectural change
    - **NOT** a retroactive redefinition of Phase 12

4. All hydration-related code **MUST**:
    - Conform strictly to **ADR-007 (Hydration Lifecycle Contract)**
    - Be fully audited and verified **before Phase 13 can be entered**

---

### Audit Implications

- Phase 12 audit records **MUST explicitly acknowledge** the presence of early hydration code.
- Any audit statement claiming “no src/** changes in Phase 12” is invalid and must be amended.
- This clarification restores alignment between:
    - `ADR-PHASE-MAP.md` (Constitutional Authority)
    - `WORK_SYSTEM.md` (Governance Handbook)
    - Actual repository state

---

### Lock Statement

🛑 **GOVERNANCE CLARIFICATION LOCKED** 🛑

This clarification is final.

- No further hydration implementation may occur outside its formally audited context.
- Entry into **Phase 13 (Release & Stability Lock)** remains **PROHIBITED**
  until all hydration code passes final verification.
- Any future deviation requires:
    - A new ADR
    - Explicit override of this clarification
    - Major version bump (v2.0+)

---

## Execution Rule

When starting or resuming work:

1. Identify the active phase from `roadmap.json`
2. Load:
    - Global Constitutional ADRs
    - Phase-specific ADRs listed above
3. Execute **ONLY** tasks belonging to the current phase
4. No redesign, no scope expansion, no ADR reinterpretation

---

## Non-Negotiable Constraint

> ADRs are **not suggestions**.  
> They are **binding architectural law**.

Any deviation requires:
- A new ADR
- Explicit rejection of the old one
- Major version bump (v2.0+)

---
