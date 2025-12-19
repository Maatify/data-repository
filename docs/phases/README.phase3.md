# Phase 3: Generic CRUD (Official)

## Status
OFFICIAL — Defined by ADRs

## Constitutional Basis
- **ADR-001 (Scope Lock)**: Defines the library as a repository abstraction layer, strictly scoped to CRUD operations. It rejects ORM/Query Builder patterns and mandates strict typing.
- **ADR-014 (Backward Compatibility)**: Enforces Semantic Versioning and prohibits breaking changes in v1.x, ensuring stability for consumers.
- **ADR-015 (Governance)**: Establishes a strict release process, requiring ADR compliance and manual approval for all changes.

## Phase Objective
The objective of Phase 3 is to establish the fundamental, unified CRUD contract across all supported storage engines. It provides a deterministic, minimal, and type-safe foundation without the complexity of advanced querying, pagination, or object hydration. This phase focuses exclusively on the core mechanics of reading and writing data.

## Implemented Capabilities
### CRUD Operations
- `find(int|string $id)`: Retrieve a single record by primary key.
- `findBy(array $filters)`: Retrieve records matching simple equality filters.
- `findAll()`: Retrieve all records from the storage.
- `insert(array $data)`: Create a new record.
- `update(int|string $id, array $data)`: Modify an existing record.
- `delete(int|string $id)`: Remove a record.

### Supported Adapters
- **MySQL**: Standard PDO-based interaction.
- **MongoDB**: Collection-based interaction.
- **Redis**: Key-value interaction (using JSON serialization).

### Expectations
- **Input/Output**: Methods accept and return native PHP arrays (`array`).
- **Error Behavior**: All driver-specific exceptions are caught and re-thrown as `Maatify\DataRepository\Exceptions\RepositoryException`.
- **Consistency**: Identical method signatures and behavior across all adapters and between Real/Fake implementations.

## Explicit Non-Goals
The following features are **EXPLICITLY NOT INCLUDED** in this phase definition:

- ✘ Pagination (any form)
- ✘ Pagination DTOs
- ✘ Count semantics standardization
- ✘ Advanced filtering (IN, LIKE, ranges, OR)
- ✘ Sorting utilities
- ✘ Hydration lifecycle
- ✘ Redis safety guards
- ✘ Redis performance optimizations
- ✘ SQL identifier quoting rules
- ✘ MongoDB ObjectId casting rules
- ✘ Interface segregation (Read / Write)

## Relationship to Legacy Phase 3 (Important)
Existing implementations observed in the repository (documented in `README.phase3beta.md`) significantly exceed this official scope by including pagination, hydration, and advanced helper utilities.

**This document supersedes all historical or observed behavior definitions.** Features present in the code but listed as "Non-Goals" above are formally classified as belonging to future roadmap phases, not this foundational Phase 3 definition.

## Compatibility Notes
- **v1.x Guarantees**: This definition adheres to ADR-014; no existing functionality is removed from the codebase, but the *official definition* of this phase is narrowed to ensure architectural clarity.
- **No Breaking Changes**: The restriction of this scope is a documentation and governance action; it does not imply breaking changes to the currently deployed library.
