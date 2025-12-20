# Phase 5 Risk Report

## Contract Risks

### 1. Implicit Contract Promotion
Several widely used methods (`paginate`, `paginateBy`, `count`, `findOneBy`) exist in the `Generic*Repository` implementations but are **absent** from the `RepositoryInterface` definition.
*   **Risk:** Formalizing these methods in `ReadRepositoryInterface` (as implied by ADR-003) constitutes a significant expansion of the contract.
*   **Impact:** Any third-party or custom repository that implements `RepositoryInterface` directly (without extending `BaseRepository` or a Generic class) will instantly break upon update, as it will lack these methods.

### 2. Interface Hierarchy Complexity
ADR-003 mandates that `RepositoryInterface` extends `ReadRepositoryInterface` and `WriteRepositoryInterface`.
*   **Risk:** While technically valid in PHP, this creates a diamond dependency if not managed carefully in documentation and usage.
*   **Impact:** Users might be confused about which interface to type-hint (`ReadRepositoryInterface` vs `RepositoryInterface`), potentially leading to inconsistent dependency injection patterns.

## Redis Capability Mismatch Risks

### 1. The `count(filters)` Violation
ADR-003 explicitly states: *"No repository may throw 'Not Supported' exceptions for contract methods."*
*   **Current State:** `GenericRedisRepository::count($filters)` throws a `RepositoryException` ("Filtering count is not supported in Redis").
*   **Risk:** If `count(filters)` becomes part of the standard `ReadRepositoryInterface`, the current Redis implementation will be in direct violation of the Constitutional ADRs.
*   **Impact:** To comply, the Redis implementation must either be fundamentally rewritten (extremely difficult for filtered counts) or the method signature/contract must be reconsidered (which risks consistency).

## Backward Compatibility Risks (ADR-014)

### 1. Strictly Typed Return Values
Phase 5 introduces strict separation. If new interfaces define stricter return types (e.g., `void` vs `bool`, or strict DTOs) than currently implemented, it will break existing implementations.
*   **Risk:** Legacy code extending current classes might rely on looser typing or covariant return types that do not match the new strict interfaces.

### 2. Constructor & Dependency Injection
While `setAdapter` is in the interface, `BaseRepository` handles it via constructor and method injection.
*   **Risk:** If the segregated interfaces imply different initialization patterns (e.g., a Read-Only repo that rejects a Write adapter), this could conflict with the existing `BaseRepository` logic which assumes a generic `AdapterInterface`.

## Test & Static Analysis Risks

### 1. Mocking Complexity
Splitting interfaces increases the complexity of setting up mocks in PHPUnit.
*   **Risk:** Tests that currently mock `RepositoryInterface` will need to ensure they mock the aggregate of all methods if the interface is composed of multiple parents.
*   **Impact:** Existing test suites (especially in consumer applications) might require updates if they mock interfaces manually instead of using PHPUnit's intersection capabilities or `createMock`.
