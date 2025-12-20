# Phase 7 — MongoDB Explicit Behavior

## Scope
This phase defines and locks the behavior for MongoDB ObjectId casting. It enforces a strict explicit-only policy to ensure predictability and consistency across real and fake drivers.

## Governing ADRs
* **ADR-005** — MongoDB ObjectId Casting Rules & Safety

## Explicit Guarantees
* **find(id)**: Automatically casts 24-character hexadecimal strings to `MongoDB\BSON\ObjectId`.
* **Explicit Only**: All other query methods (`findBy`, `paginate`, `findOneBy`) treat string values as literal strings, even if they match the ObjectId format.
* **Filter Safety**: No magic casting occurs in filter arrays. You must pass `new ObjectId(...)` explicitly if you intend to query by ObjectId in a filter.

## Explicit Non-Guarantees
* **Heuristic Detection**: The library will NEVER attempt to "guess" if a string is an ObjectId based on its content in filter contexts.
* **Implicit Filter Casting**: Passing a hex string to a field named `_id` (or mapped to it) in a filter array will NOT trigger casting.

## Audit Reference
For a detailed audit of compliance with these rules, see:
[**Phase 7 Audit Report**](../audit/PHASE7_AUDIT.md)
