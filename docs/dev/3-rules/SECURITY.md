![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🔐 Security Rules  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Define mandatory security rules for all repositories, ensuring safe handling of inputs, protection against malformed data, and secure interaction with all drivers (real + fake).

This document is MANDATORY for all repository implementations.

---

# 📚 Table of Contents

1. [Security Philosophy](#security-philosophy)  
2. [Threat Model](#threat-model)  
3. [MUST Rules](#must-rules)  
4. [MUST NOT Rules](#must-not-rules)  
5. [Untrusted Input Rules](#untrusted-input-rules)  
6. [SQL Security](#sql-security)  
7. [MongoDB Security](#mongodb-security)  
8. [Redis / Predis Security](#redis--predis-security)  
9. [Fake Driver Security](#fake-driver-security)  
10. [Normalization & Escaping](#normalization--escaping)  
11. [Sensitive Data Handling](#sensitive-data-handling)  
12. [Validation & Sanitization](#validation--sanitization)  
13. [Exception Safety](#exception-safety)  
14. [Logging Safety](#logging-safety)  
15. [Repository Hardening Checklist](#repository-hardening-checklist)  
16. [Summary](#summary)

---

# 🧠 Security Philosophy

The repository layer MUST be:

- **Strict** against malformed input  
- **Safe** from injections  
- **Deterministic** in error handling  
- **Zero-trust** → treat ALL inputs as potentially dangerous  
- **Isolated** → no raw driver access outside repository  
- **Consistent** across all drivers  

Repository MUST NEVER trust input from controllers, services, or external layers.

---

# 🎯 Threat Model

Repository MUST protect against:

- SQL injection  
- DBAL expression injection  
- MongoDB operator injection  
- Redis key injection  
- Unvalidated filters  
- Arbitrary field updates  
- Overposting attacks (`update` receiving extra fields)  
- DTO hydration injection (invalid constructor values)  
- Sensitive data leakage  
- Fake driver misbehavior  
- Type confusion attacks  
- NULL injection  
- Pagination abuse (very high limits, negative offsets)  
- Reflection-based exploitation  

---

# 🟩 MUST Rules

Repository MUST:

1. Validate ALL input  
2. Normalize ALL input  
3. Reject ANY unknown filter/payload field  
4. Reject ANY unknown operator  
5. Enforce strict typing everywhere  
6. Apply snake_case→camelCase normalization safely  
7. Disallow ANY raw query fragments  
8. Sanitize strings (trim + remove control chars)  
9. Protect ID fields (type safety: int|string ONLY)  
10. Enforce MAX_LIMIT on pagination  
11. Mask sensitive data in logs  
12. Wrap ALL driver exceptions in RepositoryException  
13. Ensure hydration safety  
14. Ensure fake drivers behave safely exactly like real ones  

---

# 🟥 MUST NOT Rules

Repository MUST NOT:

❌ accept raw SQL (even parameterized)  
❌ accept user-supplied driver commands  
❌ accept raw MongoDB operator arrays  
❌ accept Redis patterns (like KEYS *)  
❌ accept filters like: `['price' => ['raw' => '...']]`  
❌ accept ANY client-side sorting direction except ASC/DESC  
❌ accept unlimited pagination  
❌ accept unknown DTO properties  
❌ accept mixed types anywhere  
❌ pass sensitive values to logs or exceptions  

---

# ⚠ Untrusted Input Rules

ALL inputs MUST be treated as untrusted:

- `$filters`  
- `$data`  
- `$id`  
- `$pagination->filters`  
- `$pagination->sortBy`  
- DTO hydration arrays  

Repository MUST verify:

- allowed keys  
- allowed types  
- allowed operators  
- allowed sort fields  
- allowed nesting level  

If ANY of these fail → MUST throw `RepositoryException::validationError`.

---

# 🟦 SQL Security

Repository MUST:

- use prepared statements ONLY  
- NEVER include raw SQL fragments  
- NEVER construct WHERE clauses manually  
- enforce parameter binding  
- enforce strict numeric casting  
- disallow LIKE operator from external inputs  
- disallow direct ORDER BY from outside  

Forbidden:

```php
['name' => ['like' => '%a%']]   // RAW PATTERN FROM CLIENT
'sortBy' => 'id; DROP TABLE users'
'limit' => '1000000000000'
````

---

# 🟪 MongoDB Security

Repository MUST:

* disallow ANY `$operator` in input
* translate allowed operators internally only
* enforce type checking
* reject nested documents unless explicitly supported
* enforce array-of-scalars for `in/notIn`

Forbidden:

```
['age' => ['$gt' => 20]]
['email' => ['$regex' => '.*']]
['$where' => 'this.password != ""']
```

Internal translation ONLY allowed within repository code.

---

# 🔴 Redis / Predis Security

Repository MUST:

* disallow KEYS *, SCAN, raw patterns
* disallow dynamic DB switching
* sanitize keys (alphanumeric + _ : only)
* disallow binary or control characters
* reject keys longer than a safe threshold
* enforce safe TTL
* prevent flooding via large lists/hashes

Forbidden:

```
"session:*"       // wildcards
"..//etc/passwd"  // path traversal
"\0\0\0key"        // binary injection
```

---

# 🟣 Fake Driver Security

Fake drivers MUST:

* enforce same validation as real drivers
* throw identical exceptions
* reject malformed keys
* reject invalid filters
* reject invalid pagination
* enforce type casting
* avoid permissive behavior (NO "silent pass")
* ensure deterministic output

Any loose behavior in fakes → MUST FAIL CI immediately.

---

# 🧹 Normalization & Escaping

Repository MUST:

* trim all strings
* convert numeric strings to numbers
* escape special characters where required
* convert input to internal safe format
* reject invalid unicode sequences
* reject control characters (ASCII < 32)

---

# 🕵 Sensitive Data Handling

Repository MUST NOT log:

* passwords
* tokens
* session IDs
* API keys
* connection strings
* private fields
* emails (without masking)
* user-identifying keys

Sensitive output MUST be masked:

```
"email" => "user@example[hidden]"
```

---

# 🧬 Validation & Sanitization

Validation MUST ensure:

* no mixed types
* no untyped arrays
* correct generic definitions
* correct filter shapes
* safe pagination
* safe DTO hydration
* zero unexpected keys

Sanitization MUST apply before validation when needed.

---

# ⚡ Exception Safety

Repository MUST:

* catch ALL driver exceptions
* rethrow them as `RepositoryException::driverError()`
* sanitize exception messages
* NEVER expose underlying engine error messages

Wrong:

```
SQLSTATE[HY000]: Syntax error near...
```

Allowed:

```
Driver execution failed (MySQL)
```

---

# 📝 Logging Safety

Logging MUST:

* include safe metadata ONLY
* mask sensitive values
* exclude raw SQL/Mongo/Redis commands
* avoid logging very large datasets
* avoid logging before sanitization

---

# 🛡 Repository Hardening Checklist

Before releasing any repository:

* [ ] validate all inputs
* [ ] validate all filters
* [ ] validate all pagination DTOs
* [ ] validate all DTO hydrations
* [ ] normalize input
* [ ] sanitize strings
* [ ] mask sensitive logs
* [ ] safe logging rules applied
* [ ] driver exceptions wrapped
* [ ] fakes behave like real
* [ ] negative tests implemented
* [ ] SQL/Mongo/Redis unsafe paths blocked

---

# 🧩 Summary

Repository security layer ensures:

* strict input handling
* protection from injection
* safe hydration
* deterministic behavior
* masked logs
* secure driver interactions
* parity between fake and real tests

This document MUST NOT change
except through roadmap-approved updates.
