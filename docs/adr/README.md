# Architecture Decision Records (ADR)

This directory contains the **official Architecture Decision Records (ADRs)**  
for the **Maatify Data Repository** project.

These ADRs define the **non-negotiable architectural rules, constraints, and scope boundaries**
of the library.

---

## 🔒 ADR Governance Status

**ALL ADRs IN THIS DIRECTORY ARE LOCKED.**

This means:

- ❌ No ADR may be modified retroactively
- ❌ No architectural decision may be reinterpreted
- ❌ No feature or refactor may violate an ADR
- ❌ No “improvement” is allowed if it contradicts an ADR

Any change that violates an ADR requires:
- A **new major version**
- A **new ADR**
- Explicit approval under the governance rules

---

## 🎯 Purpose of ADRs

The ADRs exist to:

- Prevent architectural drift
- Eliminate ambiguity in behavior
- Protect production safety (especially Redis)
- Enforce adapter-agnostic correctness
- Ensure FakeAdapters behave identically to RealAdapters
- Provide a single source of truth for humans and AI tools

These ADRs are **part of the public contract** of the library.

---

## 📐 Scope Definition

As defined by the ADRs, this library is:

- ✅ A strict infrastructure-level data repository
- ✅ Adapter-agnostic (MySQL / MongoDB / Redis / Fake)
- ✅ Type-safe and static-analysis-first
- ❌ NOT an ORM
- ❌ NOT a Query Builder
- ❌ NOT a Redis search engine

Any attempt to use the library outside this scope is considered **misuse**.

---

## 🧭 Mandatory Compliance

All of the following MUST comply with the ADRs:

- Production code (`src/**`)
- Tests (`tests/**`)
- Documentation
- Examples
- CI pipelines
- External contributions
- AI-generated patches (Codex, Jules, etc.)

If a conflict exists between:
- Code vs ADR → **ADR wins**
- Tests vs ADR → **ADR wins**
- Documentation vs ADR → **ADR wins**

---

## 🗂 ADR Index

See the complete list of decisions here:

➡️ **[ADR-INDEX.md](./ADR-INDEX.md)**

Each ADR follows a standardized structure:
- Context
- Decision
- Consequences
- Requirements
- Rejected Alternatives

---

## 🤖 AI Enforcement Notice

AI tools working on this repository (e.g. Codex, Jules)  
**MUST treat these ADRs as immutable law**.

Any AI-generated output that violates an ADR is considered **invalid** and must be rejected.

---

## 🏁 Final Note

These ADRs are not documentation artifacts —  
they are **governance instruments**.

They exist to ensure that:

> *The library remains predictable, safe, and infrastructure-grade —  
even as contributors change over time.*

---

**Do not proceed with implementation unless you have read and understood the ADRs.**
