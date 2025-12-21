# PHASE 10 AUDIT: Error, Logging & Observability Boundaries

## Scope
**Focus Areas:**
- `src/Exceptions/`
- `src/Generic/` (Repositories)
- `src/Generic/Support/` (Ops & Builders)
- `src/Base/`
- `docs/adr/ADR-012.md`
- `docs/adr/ADR-013.md`

## ADR Compliance Matrix

| ADR | Requirement | Status | Verification Notes |
| :--- | :--- | :--- | :--- |
| **ADR-012** | **Root Exception Mandatory** | ❌ **FAIL** | Repositories leak SPL `InvalidArgumentException`. |
| **ADR-012** | **Exception Taxonomy** | ❌ **FAIL** | Missing specific exception classes (`InvalidFilterException`, `QueryExecutionException`, etc.). |
| **ADR-012** | **No Driver Leaks** | ❌ **FAIL** | Redis Driver exceptions (`RedisException`) leak in `findAll`/`paginate`. |
| **ADR-013** | **No Mandatory Logging** | ✅ **PASS** | `BaseRepository` uses optional `NullLogger`. |
| **ADR-013** | **Ops Zero Logging** | ✅ **PASS** | No logging found in `*Ops` classes. |
| **ADR-013** | **No Side Effects** | ✅ **PASS** | Logging failures (if any) do not appear to crash flow. |

## Findings

### 1. Missing Exception Taxonomy (ADR-012)
The codebase lacks the mandatory exception hierarchy defined in ADR-012. Only `RepositoryException` and `RedisSafetyException` exist.
- **Missing:** `InvalidAdapterException`, `RepositoryConfigurationException`, `QueryExecutionException`, `DriverOperationException`, `InvalidFilterException`, `InvalidPaginationException`, `HydrationException`.
- **Impact:** Callers cannot distinguish between a configuration error, a validation error, or a driver failure using `catch` blocks.

### 2. Leaking `InvalidArgumentException` (MySQL)
**File:** `src/Generic/GenericMySQLRepository.php`
**Lines:** 71 (`findBy`), 128 (`count`)
- **Detail:** `buildWhereClause()` triggers `FilterUtils` -> `MySQLFilterBuilder`, which throws `\InvalidArgumentException`.
- **Violation:**
    - In `findBy`, it is called *outside* the `try-catch` block.
    - In `count`, it is inside `try`, but `catch` only handles `\PDOException`.
- **Result:** SPL Exceptions leak to the user, bypassing `RepositoryException` wrapper.

### 3. Leaking Driver Exceptions (Redis)
**File:** `src/Generic/GenericRedisRepository.php`
**Lines:** 192, 225, 290 (`keys()` calls)
**File:** `src/Generic/Support/RedisOps.php`
**Lines:** 127 (`scanRedis`), 157 (`scanPredis`)
- **Detail:** `RedisOps::keys` calls driver methods (`scan`) directly without `try-catch`. `GenericRedisRepository` calls `keys` without `try-catch`.
- **Violation:** `RedisException` (phpredis) or `Predis\Exception` can escape `findAll`, `findBy`, and `paginate`.
- **Result:** Application crashes on Redis connection drops instead of catching `RepositoryException`.

### 4. Generic Exception Wrapping (Mongo)
**File:** `src/Generic/GenericMongoRepository.php`
- **Observation:** Uses `catch (\Exception $e)`. While this prevents leaks (Technically PASS on "No Leaks"), it swallows `LogicException` and `RuntimeException` indiscriminately, masking potential bugs as "Repository Failures". Ideally, `InvalidArgumentException` from Builders should be caught specifically and rethrown as `InvalidFilterException`.

## Verdict
**STATUS: BLOCKED**

The project has failed to implement the Error Taxonomy (ADR-012) and contains critical exception leaks in MySQL and Redis repositories. Phase 11 (Hydration) cannot proceed on this unstable foundation.

## Lock Recommendation
1.  **Implement Taxonomy:** Create the missing Exception classes in `src/Exceptions/`.
2.  **Refactor Builders:** Update Builders to throw `InvalidFilterException` OR wrap calls in Repositories to catch `InvalidArgumentException` and rethrow as `InvalidFilterException`.
3.  **Secure RedisOps:** Wrap driver calls in `RedisOps` (or the Repository) to capture `RedisException` and rethrow as `RepositoryException` (or `DriverOperationException`).
4.  **Secure MySQL:** Move `buildWhereClause` inside `try-catch` and ensure `InvalidArgumentException` is handled.
