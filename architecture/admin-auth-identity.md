# Admin Authentication & Identity Architecture
## Maatify Admin Infra

> **Authoritative Architecture Document — Phase 1 & Phase 2 (LOCKED)**

---

## 🎯 Purpose

This document defines the **authoritative architecture** for:

- Admin identity
- Authentication
- Optional TOTP (MFA)
- External integrations positioning (Telegram / OAuth)

for **Maatify Admin Infra**.

Any implementation or future phase **MUST strictly comply** with this document.

---

## 🧠 Core Principles (LOCKED)

- Composed identity (no monolithic tables)
- Secure by default
- Explicit over implicit
- No irreversible deletion
- Internal security always wins
- External services are never trusted implicitly
- Everything must be auditable

---

## 🧱 Identity Architecture (Composed Model)

Admin identity is **not stored in a single table**.
It is composed of multiple entities, each with a clear responsibility and lifecycle.

---

## 📐 Conceptual ERD (Authoritative)

---

### 1. `admins`
**Core Identity Root**

```

## admins

* id (PK)
* status
  (created | pending_verification | active | suspended | locked)
* created_at
* updated_at

```

**Rules (LOCKED):**
- ❌ No hard delete
- ❌ No soft delete
- Identity is permanent
- Status transitions are explicit and auditable

---

### 2. `admin_credentials`
**Primary Authentication Secrets**

```

## admin_credentials

* id (PK)
* admin_id (FK → admins.id)
* password_hash
* password_updated_at
* must_change_password
* created_at
* updated_at

```

**Rules (LOCKED):**
- Email + Password only
- ❌ No tokens
- ❌ No OAuth here
- One-to-one with `admins`

---

### 3. `admin_contacts`
**Contact Channels**

```

## admin_contacts

* id (PK)
* admin_id (FK → admins.id)
* type (email | phone)
* value
* is_primary
* created_at

```

**Rules:**
- One-to-many
- One primary email is mandatory
- Contacts are not implicitly verified

---

### 4. `admin_contact_verifications`
**Verification Lifecycle**

```

## admin_contact_verifications

* id (PK)
* contact_id (FK → admin_contacts.id)
* method (email | sms)
* verification_status
  (pending | verified | expired | revoked)
* verified_at
* created_at

```

**Rules (LOCKED):**
- Primary email verification is required for activation
- Verification lifecycle is independent
- ❌ No auto-verification

---

### 5. `admin_external_links`
**External Integrations (NON-AUTH)**

```

## admin_external_links

* id (PK)
* admin_id (FK → admins.id)
* provider (telegram | google | microsoft | github | ...)
* external_id
* usage (notifications_only | login_secondary_future)
* linked_at
* confirmed_at
* revoked_at

```

---

## 🔔 Telegram Policy (LOCKED)

Telegram integration is **strictly limited** to:

- Security notifications
- System alerts
- Optional OTP delivery as a **non-primary channel**

Telegram **MUST NOT** be used as:

- A login method
- A second-factor authenticator
- An identity provider
- A permission authority

> **Telegram is a messaging channel, not an authentication mechanism.**

---

### 6. `admin_preferences`
**Personal Preferences**

```

## admin_preferences

* admin_id (PK, FK → admins.id)
* language
* timezone
* locale

```

---

### 7. `admin_totp`
**Optional Second Factor (TOTP — RFC 6238)**

```

## admin_totp

* admin_id (PK, FK → admins.id)
* secret_encrypted
* is_enabled
* enrolled_at
* last_used_at
* disabled_at

```

**Rules (LOCKED):**
- Optional per admin
- Optional per system
- Presence ≠ enabled
- Enabled only after successful OTP verification
- ❌ No backup codes by default
- ❌ No SMS fallback by default

---

### 8. `system_settings`
**Global Feature Toggles**

```

## system_settings

* key (PK)
* value

```

**Critical keys:**
```

auth.totp.enabled = true | false
notifications.telegram.enabled = true | false

```

---

## 🔗 Relationships Summary

```

admins
├── 1 : 1 ── admin_credentials
├── 1 : N ── admin_contacts
│              └── 1 : N ── admin_contact_verifications
├── 1 : N ── admin_external_links
├── 1 : 1 ── admin_preferences
└── 1 : 0..1 ── admin_totp

```

---

## 🔐 Authentication Rules (LOCKED)

### Primary Authentication
- Email + Password

### Optional Multi-Factor Authentication
- TOTP (internal, RFC 6238)

---

## 🌐 OAuth Positioning (OFFICIAL DECISION)

OAuth providers (Google, Microsoft, GitHub, etc.):

### ✅ Allowed only as:
- **Secondary login convenience**
- Identity assertion only

### ❌ OAuth MUST NOT:
- Replace password authentication
- Replace internal TOTP
- Act as MFA
- Automatically create admin accounts
- Bypass permissions
- Bypass audit or security rules

---

## 🔐 OAuth + TOTP Rule (CRITICAL · LOCKED)

> **Any admin login — regardless of method (Password or OAuth) —
> MUST pass internal TOTP if it is enabled for that admin.**

This includes:
- Google Login
- Microsoft Login
- GitHub Login

OAuth **never bypasses** internal security controls.

---

## 🔁 Unified Login Flow (Authoritative)

```

[ Login Method ]
(Password | OAuth)
↓
Admin status == active ?
↓
System TOTP enabled ?
↓
Admin TOTP enabled ?
↓
Prompt TOTP
↓
Session created

```

No exceptions.
No shortcuts.

---

## 🔒 Forbidden Actions (Hard Rules)

- ❌ Admin deletion (hard or soft)
- ❌ Telegram login
- ❌ Telegram MFA
- ❌ OAuth-only admins
- ❌ External MFA trust
- ❌ Silent authentication fallback
- ❌ Auto-verification of contacts

---

## 🧾 Audit Implications (Future Phase)

The following events **must be auditable**:

- Login attempts
- Authentication failures
- TOTP enrollment / disable
- OAuth login usage
- External link changes
- Status transitions

---

## 🏁 Status

> ✅ **FINAL · LOCKED · AUTHORITATIVE**

All future architecture, implementation, and extensions **must conform** to this document.

---

**Library:** `maatify/admin-infra`
**Phase Coverage:** Phase 1 (Identity) + Phase 2 (Authentication)
