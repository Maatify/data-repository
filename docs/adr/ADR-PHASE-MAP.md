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
