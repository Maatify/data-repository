# PHASE 8 AUDIT: Pagination & Count Semantics

## 1. Scope & Objectives
**Phase:** Phase 8 (Pagination & Count Semantics)
**Focus:** `paginate()`, `count()`, `PaginationResultDTO` consistency across MySQL, MongoDB, and Redis.
**Sources:** `docs/adr/ADR-010.md`, `docs/adr/ADR-011.md`, `src/Generic/*`.

## 2. ADR Compliance Matrix

| Component | Requirement | Status | Notes |
|-----------|-------------|--------|-------|
| **ADR-010** | Return `PaginationResultDTO` only | ✅ PASS | All `paginate*` methods return strict DTO. |
| **ADR-010** | Structure (items, total, page, perPage, pages) | ✅ PASS | Handled via `PaginationHelper::buildMeta` and DTO constructor. |
| **ADR-010** | Deterministic (`pages = ceil(total/perPage)`) | ✅ PASS | Centralized in `PaginationHelper`. |
| **ADR-011** | `count()` is filter-scoped | ⚠️ FAIL | MySQL/Mongo pass. Redis `count($filters)` throws Exception. |
| **ADR-011** | `count($filters)` vs `paginateBy($filters)` parity | ⚠️ FAIL | Redis `paginateBy($filters)` returns correct `total`, but `count($filters)` throws. |
| **ADR-011** | Redis Safety (SCAN-based, Bounded) | ✅ PASS | `RedisOps::keys` enforces safety limits. |
| **Phase 6** | `LimitOffsetValidator` Usage | ✅ PASS | Validated in all `paginate*` methods. |

## 3. Findings

### 3.1. Redis Count Inconsistency (Critical)
**Issue:**
`GenericRedisRepository::count(array $filters)` throws a `RepositoryException('Filtering count is not supported in Redis.')`.

**Contradiction:**
`GenericRedisRepository::paginateBy(array $filters)` **successfully** calculates the total count of filtered items:
```php
$allFiltered = $this->findBy($filters, $orderBy);
$total = count($allFiltered); // <--- Total is known here!
// ...
return new PaginationResultDTO($data, $pagination);
```

**Violation:**
ADR-011 states:
> `PaginationResultDTO.total` **must equal** the result of `countBy(filters)`.

Currently, `paginateBy` provides the total, but calling `count($filters)` (which should return that same total) fails. This prevents consumers from checking the count without paginating, leading to inconsistent API behavior compared to MySQL and Mongo.

### 3.2. Pagination DTO Parity
All drivers successfully import and use `Maatify\Common\Pagination\DTO\PaginationResultDTO` and `Maatify\Common\Pagination\Helpers\PaginationHelper`, ensuring uniform metadata structure and calculation.

## 4. Verdict

**Status:** 🔴 **FAIL / BLOCKED**

Phase 8 cannot be considered verified until `GenericRedisRepository::count()` is aligned with `paginateBy()` logic.

---

## 5. Blueprint for Remediation

### Goal
Enable `count(array $filters)` in `GenericRedisRepository` to return the count of filtered items, matching the logic already present in `paginateBy`.

### Implementation Plan

**Location:** `src/Generic/GenericRedisRepository.php`

**Change:**
Refactor `count()` to support filters by delegating to the existing in-memory filtering logic (or `findBy`), consistent with `paginateBy`.

**Logic:**
1.  Check if `$filters` is empty.
    *   If **Yes**: Return `count($this->getRedisOps()->keys($this->keyPrefix . '*'))` (Existing behavior).
    *   If **No**:
        1.  Call `$this->findBy($filters)`.
        2.  Return `count($results)`.

**Safety Note:**
This operation is "safe" within the constraints of ADR-006 because `findBy` relies on `findAll`, which relies on `RedisOps::keys`. `RedisOps::keys` already enforces the safety limit (SCAN limit). If the dataset exceeds the limit, it throws an exception, satisfying the "Fail Fast" requirement of ADR-011.

**Verification:**
1.  Unlock `tests/Legacy/Redis/RedisFilteringTest.php` or create a new test `tests/Generic/NoSQL/RedisCountTest.php`.
2.  Assert `count($filters)` equals `paginateBy($filters)->pagination->total`.
