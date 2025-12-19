# Phase 3: CRUD Layer (Official)

## Status
OFFICIAL — Scope Frozen

## Architectural Authority
- **ADR-001 (Scope Lock)**: Defines the library as a repository abstraction layer, strictly scoped to CRUD operations. It rejects ORM/Query Builder patterns and mandates strict typing.
- **ADR-014 (Backward Compatibility)**: Enforces Semantic Versioning and prohibits breaking changes in v1.x, ensuring stability for consumers.
- **ADR-015 (Governance)**: Establishes a strict release process, requiring ADR compliance and manual approval for all changes.

## Purpose
Phase 3 exists to establish the fundamental, unified CRUD contract across all supported storage engines. It is intentionally limited to provide a deterministic, minimal, and type-safe foundation without the complexity of advanced querying, pagination, or object hydration. This phase focuses exclusively on the core mechanics of reading and writing data, ensuring that subsequent phases build upon a stable and predictable base.

## What Phase 3 Provides
### Allowed CRUD Operations
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

### Behavior
- **Input/Output**: Methods accept and return native PHP arrays (`array`) only.
- **Error Behavior**: All driver-specific exceptions are caught and re-thrown as `Maatify\DataRepository\Exceptions\RepositoryException`.
- **Consistency**: Identical method signatures and behavior across all adapters and between Real/Fake implementations.

## What Phase 3 Explicitly Forbids
The following features are **EXPLICITLY NOT INCLUDED** and are considered OUT OF SCOPE for this phase:

- ✘ Pagination of any kind
- ✘ Pagination DTOs
- ✘ Count semantics standardization
- ✘ Advanced filtering or sorting
- ✘ Hydration lifecycle
- ✘ Redis performance tricks
- ✘ Redis relational assumptions
- ✘ SQL quoting rules
- ✘ Mongo ObjectId casting rules
- ✘ Interface segregation

## Relationship to Legacy Implementations
Existing implementations observed in the repository (documented in `README.phase3beta.md`) significantly exceed this official scope by including pagination, hydration, and advanced helper utilities.

**This document supersedes all historical or observed behavior definitions.** Features present in the code but listed as "What Phase 3 Explicitly Forbids" above are formally classified as belonging to future roadmap phases or legacy drift, not this foundational Phase 3 definition.

## Stability & Compatibility
- **v1.x Guarantees**: This definition adheres to ADR-014; no existing functionality is removed from the codebase, but the *official definition* of this phase is narrowed to ensure architectural clarity.
- **No Breaking Changes**: The restriction of this scope is a documentation and governance action; it does not imply breaking changes to the currently deployed library.
