# Phase 11 — Hydration Contracts & Pipeline Definition

## Status
**ARCHITECTURALLY COMPLETE — DOCUMENTATION LOCK**

This phase formalizes and locks the **hydration contract layer** of the
`maatify/data-repository` package.

No hydration logic is implemented in this phase.
Only **interfaces, context objects, and lifecycle definitions** are introduced
and locked.

---

## Purpose

Phase 11 establishes a **strict, explicit contract** for hydration before any
implementation phases.

This ensures:
- Deterministic hydration behavior
- Zero implicit casting or mapping
- Clear separation between **contract**, **implementation**, and **policy**
- Full compliance with the “Documentation as Code” principle

---

## Scope (Phase 11)

### Included
- Hydrator contract definition
- Hydration context definition
- Locked hydration lifecycle (pipeline stages)
- Exception boundary for hydration failures

### Explicitly Excluded
- Hydrator implementations (Phase 12)
- Casting rules (Phase 13)
- Mapping profiles implementation (Phase 14)
- Pagination + hydration integration (Phase 17)

---

## ADR Alignment

This phase is governed by the following ADRs:

| ADR | Title | Relevance |
|----|------|-----------|
| ADR-001 | Architecture Boundaries | Hydration is fully decoupled from repositories, adapters, and drivers |
| ADR-007 | Hydration Lifecycle | Defines the 5-stage locked hydration pipeline |
| ADR-014 | No Implicit Behavior | Hydration must be explicitly configured |
| ADR-015 | Fake vs Real Parity | Hydration contracts apply equally to real and fake repositories |
| ADR-016 | Explicit Contracts | All hydration behavior must be contract-driven |

---

## Hydration Contracts

### HydratorInterface

The `HydratorInterface` defines the **only allowed entry point** for hydration.

Key rules:
- Strictly typed using generics (`@template T`)
- Accepts raw array input
- Produces a fully hydrated object of type `T`
- Does not depend on repositories, adapters, or drivers
- Contains no convenience helpers or implicit behavior

Conceptual contract:
- `hydrate(array $row, ?HydrationContext $context): T`
- `hydrateAll(array $rows, ?HydrationContext $context): array<T>`

The interface is **LOCKED** after this phase.

---

### HydrationContext

`HydrationContext` is a **passive configuration object** (DTO).

It contains **no logic** and performs **no hydration work**.

Allowed responsibilities:
- Define active pipeline stages
- Carry arbitrary metadata
- Hold a reference to a mapping profile
- Provide contextual information to the hydrator

Disallowed responsibilities:
- No casting
- No mapping
- No validation
- No driver or adapter awareness

---

## Hydration Pipeline (Locked)

The hydration lifecycle is strictly defined and locked as a **5-stage pipeline**
as mandated by ADR-007.

| Stage    | Purpose                                |
|----------|----------------------------------------|
| PREPARE  | Normalize and clean raw input          |
| CAST     | Enforce data types                     |
| MAP      | Assign data to object properties / DTO |
| VALIDATE | Apply validation rules                 |
| COMPLETE | Finalization and post-processing       |

These stages are represented as constants and **must not be reordered,
removed, or implicitly skipped**.

---

## Exception Boundary

All hydration-related failures must result in a `HydrationException`
(or a subclass).

Rules:
- No silent failures
- No `null` returns
- No leaking of lower-level exceptions
- Messages must be deterministic and non-driver-specific

---

## Integration Points

### Input
- Raw arrays produced by:
  - Generic repositories
  - Result normalizers
  - Fake repositories

### Output
- Fully hydrated objects (`T`)
- Arrays of hydrated objects (`array<T>`)

### Configuration
- `HydrationContext` is passed explicitly from the caller
- No implicit default hydration behavior exists

---

## Phase Lock

After Phase 11:
- `HydratorInterface` is **LOCKED**
- `HydrationContext` structure is **LOCKED**
- Pipeline stage definitions are **LOCKED**

Any future changes require:
- A new ADR
- A new major version (if breaking)

---

## Next Phase

**Phase 12 — Hydrator Implementation**

Phase 12 will introduce:
- Concrete hydrator implementations
- Controlled execution of the pipeline
- Still no implicit behavior

---

© 2025 Maatify.dev  
Engineered by **Mohamed Abdulalim**
