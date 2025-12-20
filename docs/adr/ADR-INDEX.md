# Architecture Decision Records — Index

This document lists all **locked Architecture Decision Records (ADRs)**
for the **Maatify Data Repository** project.

All decisions listed here are **final and non-negotiable**.

---

## 📜 ADR List

| ID      | Title                                             | Status |
|---------|---------------------------------------------------|--------|
| ADR-001 | Library Scope & Non-Goals                         | LOCKED |
| ADR-002 | Redis Behavior & Limitations                      | LOCKED |
| ADR-003 | Interface Segregation (Read / Write Contracts)    | LOCKED |
| ADR-004 | SQL Identifier Quoting Consistency                | LOCKED |
| ADR-005 | MongoDB ObjectId Casting Rules                    | LOCKED |
| ADR-006 | Redis Safety Limits & Runtime Guards              | LOCKED |
| ADR-007 | Hydration Lifecycle Contract                      | LOCKED |
| ADR-008 | Fake Adapters & Testing Guarantees                | LOCKED |
| ADR-009 | RepositoryResolver Scope & Service Locator Risks  | LOCKED |
| ADR-010 | Pagination DTO & Result Guarantees                | LOCKED |
| ADR-011 | Count Semantics & Consistency                     | LOCKED |
| ADR-012 | Error & Exception Taxonomy                        | LOCKED |
| ADR-013 | Logging & Observability Boundaries                | LOCKED |
| ADR-014 | Backward Compatibility & Deprecation Policy       | LOCKED |
| ADR-015 | Release Process & Governance                      | LOCKED |
| ADR-016 | Repository Contract Boundary (v1.x Clarification) | LOCKED |
---

## 🔒 Governance Notice

Any change that contradicts an ADR listed above requires:
- A new major version
- A new ADR
- Explicit governance approval

If there is a conflict between code, tests, or documentation and an ADR,
**the ADR always takes precedence**.
