# Phase 10 — Error, Logging & Observability Boundaries

## Status
**COMPLETED & LOCKED**

---

## Purpose

Phase 10 formalizes **ADR-012** and **ADR-013** across the codebase:

- A mandatory **root exception** (`RepositoryException`) for all repository-facing failures
- A stable **exception taxonomy** (typed exceptions) to prevent ambiguous catch blocks
- A strict **no-leak guarantee**: no driver / SPL / vendor exceptions escape repositories
- Explicit boundaries for **logging & observability**:
  - Logging is optional
  - Ops layer performs **zero logging**
  - Logging failure must never crash flow

This phase is a **stability & safety boundary** and is required before Hydration (Phase 11).

---

## Scope

**Primary areas:**
- `src/Exceptions/`
- `src/Generic/` (Generic repositories)
- `src/Generic/Support/` (Ops & Builders - boundaries only)
- `src/Base/`
- ADRs:
  - `docs/adr/ADR-012.md`
  - `docs/adr/ADR-013.md`
- Audit:
  - `docs/audit/PHASE10_AUDIT.md`

---

## Exception Taxonomy (ADR-012)

All exceptions MUST ultimately extend:

- `Maatify\DataRepository\Exceptions\RepositoryException`

**Typed categories introduced/used:**
- `RepositoryConfigurationException`
  - `InvalidAdapterException`
- `InvalidFilterException`
- `InvalidPaginationException`
- `QueryExecutionException` (query/driver execution failures in SQL paths)
- `DriverOperationException` (driver operation failures in NoSQL/Redis paths)
- `UnsafeOperationException` (operation is not allowed / unsafe by contract)
- `HydrationException`
  - `HydrationValidationException`
- `RedisSafetyException`
  - `RedisSafetyLimitExceededException` (safety/limit violation)

**Rule:** callers must be able to distinguish validation/config/driver failures by type.

---

## No-Leak Guarantee (ADR-012)

Repositories MUST:
- wrap driver calls in `try/catch (\Throwable $e)`
- rethrow **only** typed Repository exceptions
- preserve chain via previous exception (`0, $e`)
- expose **deterministic messages** (no vendor message leaking)

### Deterministic Message Policy

- Public message MUST be static and predictable
- Driver/vendor message MUST NOT be included in the public message
- Original exception MUST be preserved as `$previous`

Examples:
- ✅ `new QueryExecutionException('Find operation failed.', 0, $e)`
- ✅ `new DriverOperationException('Insert operation failed.', 0, $e)`
- ❌ `"Find failed: " . $e->getMessage()`

---

## Pagination Exception Classification

Pagination methods MUST avoid misclassification:

- Validation/contract failures:
  - thrown as `InvalidPaginationException` (or a RepositoryException subtype as defined by validator)
- Driver/query failures:
  - MUST remain:
    - `QueryExecutionException` (MySQL)
    - `DriverOperationException` (Redis/Mongo)

**Rule:** pagination code must not convert a driver failure into a validation failure.

---

## Logging & Observability Boundaries (ADR-013)

- Logging is optional.
- `BaseRepository` may accept a logger and default to `NullLogger`.
- Ops (`*Ops`) MUST never log.
- No logging side effects are allowed to crash the repository behavior.

---

## Verification

- `docs/audit/PHASE10_AUDIT.md` exists and reflects **PASS** status.
- PHPUnit test suite passes after aligning:
  - exception types
  - deterministic messages
  - pagination classification rules
- PHPStan level max passes (including tests).

---

## Lock Statement

Phase 10 is **LOCKED**.

Allowed changes only if:
- ADR-012 / ADR-013 changes
- security fix requiring exception behavior changes
- driver behavior changes

Forbidden:
- reintroducing driver message leakage
- reintroducing SPL/vendor exception leaks
- adding mandatory logging
- adding Ops logging

---

## Next Phase

➡ **Phase 11 — Hydration Pipeline & Deterministic DTO Mapping**
