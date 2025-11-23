![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🧪 Validation Rules  
**Project:** maatify/data-repository**  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** This document defines the complete validation strategy for all Repository insert/update operations using the validation tools from `maatify/common`.

---

# 📚 Table of Contents

1. [Validation Philosophy](#validation-philosophy)  
2. [Where Validation Happens](#where-validation-happens)  
3. [When Validation Runs](#when-validation-runs)  
4. [Validation Sources](#validation-sources)  
5. [MUST Rules](#must-rules)  
6. [MUST NOT Rules](#must-not-rules)  
7. [Insert Validation](#insert-validation)  
8. [Update Validation](#update-validation)  
9. [Filter Validation](#filter-validation)  
10. [Pagination Validation](#pagination-validation)  
11. [DTO/Hydration Validation](#dtohydration-validation)  
12. [RepositoryException & Validation Errors](#repositoryexception--validation-errors)  
13. [Testing Validation](#testing-validation)  
14. [Summary](#summary)

---

# 🧠 Validation Philosophy

Validation inside the repository layer MUST guarantee:

- **Safety** from malformed input  
- **Consistency** across all drivers (MySQL/DBAL/Mongo/Redis)  
- **Predictability** for API & business layers  
- **Strict typing** enforced before sending data to the driver  
- **Normalization** before persistence  

Repository-level validation is not business validation.  
Business logic happens above the repository layer.

---

# 📌 Where Validation Happens

Validation MUST run inside these methods:

| Method         | Validation Required |
|----------------|----------------------|
| `insert()`     | YES – full validation |
| `update()`     | YES – update-specific |
| `delete()`     | NO – but must validate ID type |
| `find()`       | NO – only filter validation |
| `findBy()`     | YES – filter validation |
| `findAll()`    | NO |
| pagination     | YES – pagination DTO validation |

---

# ⏱ When Validation Runs

Validation MUST occur:

1. **Before hydration**  
2. **Before any driver operation**  
3. **After filtering/normalization**  
4. **Before converting data to SQL/Mongo/Redis formats**

Validation MUST NEVER occur after execution  
and MUST NEVER modify driver output.

---

# 🧩 Validation Sources

Repository validation uses validation utilities provided by:

```

maatify/common

````

Including:

- `Validator`  
- `FilterValidator`  
- `PaginationValidator`  
- `TypedArrayValidator`  
- `IdValidator`  
- DTO hydrator-level validation  

---

# 🟩 MUST Rules

Validation MUST:

1. Enforce strict types on all inputs  
2. Validate ID: `int|string` only  
3. Validate arrays with generic constraints  
4. Validate filters before applying them  
5. Validate pagination DTO  
6. Validate update payload only for keys being updated  
7. Validate insert payload must include required fields  
8. Validate nested structures using recursive rules  
9. Throw `RepositoryException` on validation failure  
10. Normalize empty strings → null when needed  
11. Normalize snake_case → camelCase before hydration  
12. Protect against untrusted input (external callers)  

---

# 🟥 MUST NOT Rules

Validation MUST NOT:

❌ enforce business rules  
❌ run database queries  
❌ validate relationships (foreign keys)  
❌ perform transformation logic  
❌ modify global state  
❌ run hydration inside validators  
❌ accept `mixed` values  
❌ allow untyped arrays  
❌ accept fields not defined in the repository schema  

---

# 🧱 Insert Validation

Insert operations MUST validate:

- required keys  
- allowed keys only  
- types of all values  
- nested DTO requirements  
- uniqueness constraints only if provided by adapter (NOT repository)  
- default values if missing  

Example:

```php
$validator->require(['name', 'email']);
$validator->string('name')->max(255);
$validator->email('email');
````

If invalid → MUST throw `RepositoryException::validationError`.

---

# 🛠 Update Validation

Update MUST validate only the fields being changed.

Rules:

* If `$id` invalid → throw
* If `$data` empty → throw
* If unsupported fields exist → throw
* Validate each field using same rules as insert
* Allow partial updates
* Default values MUST NOT be applied here

---

# 🔍 Filter Validation

All filters MUST be validated against:

* allowed operators
* allowed fields
* correct value types
* correct array generics
* safe comparison rules

Examples of allowed patterns:

```
['status' => 'active']
['id' => ['in' => [1, 2, 3]]]
['age' => ['gt' => 20]]
```

Forbidden:

```
['unknown' => 123]
['price' => ['contains' => ['x','y']]]   // wrong type
['name' => ['like' => '%x%']]            // not supported filter
```

---

# 📄 Pagination Validation

Before calling repository with pagination,
PaginationDTO MUST be validated using:

* page (>=1)
* limit (>=1)
* max limit enforcement
* offset computed safely

If pagination DTO invalid → throw `RepositoryException`.

---

# 🧬 DTO/Hydration Validation

Hydrator MUST:

* verify DTO type exists
* verify constructor parameters match repository output
* validate property types
* apply default values
* normalize snake_case → camelCase

If mismatch → must throw `RepositoryException::hydrationError`.

---

# ⚠ RepositoryException & Validation Errors

Validation failure MUST be wrapped into:

```
RepositoryException::validationError(string $message)
```

Message MUST include:

* invalid field
* expected type
* received value description
* path if nested

---

# 🧪 Testing Validation

Validation MUST be tested in both:

### ✔ Fake Tests

Using:

* FakeMySQLAdapter
* FakeMongoAdapter
* FakeRedisAdapter
* FakeResolver

### ✔ Real Tests

Using:

* PDO + DBAL
* Redis + Predis
* MongoDB

Tests MUST cover:

* required keys missing
* unexpected keys
* type mismatch
* invalid filters
* invalid pagination
* invalid hydration

---

# 🧩 Summary

Repository validation layer ensures:

* correct and safe input
* stable hydration
* predictable behavior
* strict type compliance
* protection against malformed/untrusted data

This document MUST NOT change
except through roadmap updates.
