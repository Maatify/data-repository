# Phase 12 — Design Philosophy & Architectural Intent

## 1. Purpose of This Phase

Phase 12 serves as the definitive **Philosophy & Governance Lock** for the `maatify/data-repository` project.

While Phase 11 established the concrete interfaces and contracts, Phase 12 exists to document and lock the *intent* and *reasoning* behind those contracts. This distinction is critical: interfaces define *what* the code does, but philosophy defines *why* it does it and *how* it must evolve.

This phase is strictly non-functional. It introduces no code, no APIs, and no behavior changes. Instead, it establishes the immutable laws that govern Phase 13 (Implementation) and all subsequent phases. It ensures that the library's future development remains aligned with its original architectural vision, preventing scope creep and "magic" behavior.

## 2. What This Library IS

This library is a strict, infrastructure-level repository abstraction designed for high-reliability environments.

*   **Infrastructure Abstraction:** It provides a consistent boundary between domain logic and storage engines.
*   **Deterministic Behavior:** Operations behave predictably across all supported drivers (MySQL, MongoDB, Redis).
*   **Fake == Real Parity:** In-memory "Fake" repositories are first-class citizens, guaranteeing that tests passing against fakes will pass against real infrastructure.
*   **Safety-First Design:** It prioritizes system stability and memory safety over developer convenience, explicitly guarding against dangerous operations (e.g., unbounded Redis scans).

## 3. What This Library IS NOT (CRITICAL)

To maintain its integrity, this library explicitly defines what it is **NOT**. These constraints are absolute.

*   **NOT an ORM:** It does not manage object relationships, lazy loading, or dirty state tracking.
*   **NOT a Query Builder:** It does not provide a fluent interface for constructing arbitrary SQL or NoSQL queries.
*   **NOT Active Record:** Entities are plain data objects (DTOs) and do not know how to save themselves.
*   **NOT a Service Locator:** Dependencies must be injected explicitly; global resolution is forbidden.
*   **NOT an Auto-magic Hydration Engine:** Hydration is an explicit, multi-stage pipeline, not a reflection-based guessing game.
*   **NOT a Retry / Fallback / Resilience Layer:** It fails fast and deterministically; resilience is the responsibility of the consumer.
*   **NOT a Business Logic Layer:** Repositories handle data access only; they strictly enforce architectural boundaries.

## 4. Core Locked Principles

The following principles are **LOCKED**. Any deviation constitutes a violation of the library's core philosophy.

1.  **Explicit over Implicit**
    *   Configuration and behavior must be declared, not assumed. Implicit defaults are minimized.
    *   *Ref: ADR-001, ADR-007*

2.  **Safety over Convenience**
    *   The library will refuse to execute unsafe operations (e.g., blocking Redis keys, unbounded queries) even if it requires more verbose code from the user.
    *   *Ref: ADR-002, ADR-006*

3.  **Deterministic Failures**
    *   Errors must be specific, typed, and predictable. Silent failures or generic "catch-all" behavior are forbidden.
    *   *Ref: ADR-001, ADR-012*

4.  **No Magic**
    *   Behavior must be traceable through the code. runtime reflection magic, "smart" guessing, and hidden side effects are prohibited.
    *   *Ref: ADR-001, ADR-007*

5.  **Infrastructure-only Responsibility**
    *   The library concerns itself solely with data access mechanics. It does not validate business rules or manage domain state.
    *   *Ref: ADR-001, ADR-016*

6.  **Adapter / Repository / Driver Separation**
    *   Repositories define *what* data is needed. Adapters define *how* to talk to storage. Drivers are the low-level transport. These layers must never leak into each other.
    *   *Ref: ADR-001, ADR-008*

7.  **Fake == Real Semantics**
    *   "Fake" adapters are not mocks; they are fully functional in-memory implementations that strictly adhere to the same behavioral contracts as real drivers.
    *   *Ref: ADR-008*

## 5. Architectural Boundaries

The architecture is defined by rigid boundaries that enforce separation of concerns:

*   **Repository vs Adapter vs Driver:** The Repository defines the application's data needs (e.g., `findUserById`). The Adapter translates this request into a storage-specific command (e.g., SQL `SELECT` or Redis `GET`). The Driver (PDO, Phpredis) executes the command.
*   **Repository vs Hydration:** The Repository retrieves raw data. The Hydrator transforms that raw data into Domain Objects. These are distinct lifecycle stages.
*   **Ops vs Business Logic:** "Ops" classes (e.g., `MysqlOps`) handle low-level data normalization (like casting `last_insert_id`). They contain no business rules.
*   **Pagination vs Query Logic:** Pagination is a post-processing concern handled by DTOs and Validators. It is decoupled from the core query execution logic to ensure consistent behavior across drivers.

## 6. ADR References

This philosophy is grounded in the following Architecture Decision Records:

*   **ADR-001:** DataRepository Scope Lock & Refinement Strategy
*   **ADR-002:** Redis Repository Behavior & Safety Constraints
*   **ADR-006:** Redis Safety Limits & Runtime Guards
*   **ADR-007:** Hydration Lifecycle Contract
*   **ADR-014:** Backward Compatibility & Deprecation Policy
*   **ADR-015:** Release Process & Governance
*   **ADR-016:** Repository Contract Boundary

## 7. Lock Statement

🛑 **PHILOSOPHY LOCKED** 🛑

The design philosophy detailed in this document is now **LOCKED**.

*   Any violation of these principles is considered a **BREAKING CHANGE**.
*   All future phases (Phase 13+) must strictly comply with this document.
*   Pull requests or architectural proposals that contradict these principles must be rejected or accompanied by a formal ADR overriding this lock (Major Version change).
