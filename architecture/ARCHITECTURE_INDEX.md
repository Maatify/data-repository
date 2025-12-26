# Architecture Index
## Maatify Admin Infra

> **Authoritative Architecture Index — FINAL · LOCKED**

This document is the **single entry point** for all architectural decisions,
documents, and phases of **Maatify Admin Infra**.

Any implementation, extension, or future decision **MUST conform** to the
architecture defined and indexed here.

---

## 🧭 Project Overview

**Library Name:** `maatify/admin-infra`
**Type:** Admin Infrastructure Library
**Scope:** Backend infrastructure only (no UI, no framework dependency)

**Core Responsibilities:**
- Admin identity & lifecycle
- Authentication & optional MFA
- Authorization (roles & permissions)
- Sessions, devices & impersonation
- Audit & activity logging
- Notifications (pluggable channels)
- System settings & feature flags
- Operations, exports & observability

---

## 🧱 Architectural Phases Index

The architecture is defined across **progressive, locked phases**.
Each phase builds on the previous one and must not contradict it.

---

### ✅ Phase 0 — Vision & Boundaries

**File:**
```

docs/architecture/admin-vision.md

```

**Covers:**
- Vision & purpose
- Scope & non-goals
- Security & architectural principles
- No-delete policy
- External services positioning

**Status:** FINAL · LOCKED

---

### ✅ Phase 1 & 2 — Admin Identity & Authentication

**File:**
```

docs/architecture/admin-auth-identity.md

```

**Covers:**
- Composed admin identity model
- Credentials separation
- Contact verification
- Optional TOTP (RFC 6238)
- OAuth positioning (secondary only)
- OAuth + TOTP enforcement
- Telegram = notifications only

**Status:** FINAL · LOCKED

---

### ✅ Phase 3 — Authorization (Roles & Permissions)

**File:**
```

docs/architecture/admin-authorization.md

```

**Covers:**
- Permission model
- Role model
- Role-permission mapping
- Admin-role assignments
- Ability-based checks
- No implicit super-admin

**Status:** FINAL · LOCKED

---

### ✅ Phase 4 — Audit & Activity

**File:**
```

docs/architecture/admin-audit-activity.md

```

**Covers:**
- Audit principles
- Event taxonomy
- Append-only logging
- Non-blocking behavior
- Integration with `maatify/mongo-activity`
- Retention & TTL
- Failure handling

**Status:** FINAL · LOCKED

---

### ✅ Phase 5 — Notifications (Extensible)

**File:**
```

docs/architecture/admin-notifications.md

```

**Covers:**
- Notification abstraction
- Channel interface
- Telegram & Email (initial)
- Future channels (WhatsApp, Slack, SMS)
- Admin preferences
- Security notification guarantees

**Status:** FINAL · LOCKED

---

### ✅ Phase 6 — System Settings & Feature Flags

**File:**
```

docs/architecture/admin-system-settings.md

```

**Covers:**
- Global system settings
- Security toggles
- Feature flags
- Access control
- Audit of changes
- Fail-safe defaults

**Status:** FINAL · LOCKED

---

### ✅ Phase 7 — Sessions, Devices & Security

**File:**
```

docs/architecture/admin-sessions-security.md

```

**Covers:**
- Session lifecycle
- Device identification & trust
- Session revocation
- Impersonation
- Emergency security mode
- Notifications & audit integration

**Status:** FINAL · LOCKED

---

### ✅ Phase 8 — Operations, Exports & Observability

**File:**
```

docs/architecture/admin-ops-observability.md

```

**Covers:**
- Operational actions
- Async exports
- Secure delivery
- Logs, metrics & health
- Observability boundaries
- Permission enforcement

**Status:** FINAL · LOCKED

---

## 🔒 Global Locked Decisions (Summary)

- ❌ No admin deletion (hard or soft)
- ❌ No implicit permissions
- ❌ No external auth trust
- ❌ No OAuth-only admins
- ❌ No Telegram login or MFA
- ❌ No blocking side effects
- ❌ No framework dependency

- ✅ Internal TOTP enforced if enabled
- ✅ Audit is append-only
- ✅ Notifications are pluggable
- ✅ All privileged actions are auditable
- ✅ Emergency security controls exist

---

## 🧾 Governance Rules

- Any new phase **must reference this index**
- Any contradiction invalidates the change
- Any exception requires explicit ADR
- No architectural decision lives outside this index

---

## 🏁 Architecture Status

> ✅ **ARCHITECTURE COMPLETE · STABLE · LOCKED**

The architecture of **Maatify Admin Infra** is now:
- Fully defined
- Internally consistent
- Extensible
- Ready for implementation planning

---

## 🔜 Next Logical Steps (Optional)

- ADR Index (if needed)
- Implementation roadmap
- Test strategy blueprint
- Package splitting plan

---

© 2025 Maatify
**Library:** `maatify/admin-infra`
