![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🔍 Filtering Rules  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Define the official filtering structure used by all Base* repositories.  
Filtering must work consistently across MySQL, DBAL, MongoDB, Redis, and Fake drivers.

---

# 📚 Table of Contents

1. [Filtering Philosophy](#filtering-philosophy)  
2. [Filter Syntax](#filter-syntax)  
3. [Allowed Operators](#allowed-operators)  
4. [Forbidden Operators](#forbidden-operators)  
5. [Scalar Filters](#scalar-filters)  
6. [Array (Advanced) Filters](#array-advanced-filters)  
7. [Generic Filter Types](#generic-filter-types)  
8. [Validation Rules](#validation-rules)  
9. [Filter Normalization](#filter-normalization)  
10. [Driver Compatibility](#driver-compatibility)  
11. [Fake Driver Filtering](#fake-driver-filtering)  
12. [Testing Filtering](#testing-filtering)  
13. [Summary](#summary)

---

# 🧠 Filtering Philosophy

Filtering MUST be:

- **Unified** (نفس الصيغة لكل الدرايفرز)  
- **Typed** (typed generics for arrays)  
- **Safe** (لا يسمح بأي operator خارج المواصفات)  
- **Normalized** (snake_case → camelCase → column driver rules)  
- **Portable** (works for MySQL/DBAL/Mongo/Redis)  
- **Deterministic** (fake results identical to real ones)

Repository MUST NOT accept raw SQL, raw Redis commands, or raw Mongo expressions.  
Repository MUST build internal driver-safe logic only.

---

# 🟦 Filter Syntax

Filters MUST use ONLY these formats:

### 1) Simple Equality
```php
['status' => 'active']
['id' => 5]
````

### 2) Advanced Operators

```php
['age' => ['gt' => 20]]
['id' => ['in' => [1,2,3]]]
['email' => ['contains' => '@gmail.com']]
```

### 3) Combined Filters

```php
[
  'status' => 'active',
  'age' => ['gte' => 21]
]
```

Parent-level logical operators (OR groups) will come in Repository v2.
This version supports AND filtering only.

---

# 🟩 Allowed Operators

| Operator     | Meaning          | Works On    |
|--------------|------------------|-------------|
| `eq`         | equal            | ALL drivers |
| `ne`         | not equal        | ALL         |
| `gt`         | greater than     | numeric     |
| `gte`        | >=               | numeric     |
| `lt`         | <                | numeric     |
| `lte`        | <=               | numeric     |
| `in`         | array "in" check | ALL         |
| `notIn`      | array not in     | ALL         |
| `contains`   | substring match  | string      |
| `startsWith` | prefix match     | string      |
| `endsWith`   | suffix match     | string      |
| `exists`     | field exists?    | ALL         |

---

# 🟥 Forbidden Operators

These MUST NEVER be allowed:

| Forbidden    | Reason                       |
|--------------|------------------------------|
| `like`       | SQL injection surface        |
| `regex`      | Mongo-only, nonportable      |
| `$gt` `$lt`  | Mongo-raw syntax forbidden   |
| `raw`        | raw SQL/Mongo expressions    |
| `or` / `and` | Reserved for v2 builder      |
| `between`    | NOT universal                |
| `match`      | full-text search unsupported |

If any forbidden operator is passed → MUST throw `RepositoryException::invalidFilter`.

---

# 🔵 Scalar Filters

Simple example:

```php
['status' => 'active']
```

Validation:

* `status` MUST be string
* key MUST be known in repository schema
* no nested array allowed

---

# 🟠 Array (Advanced) Filters

For:

```
['age' => ['gt' => 20]]
```

Validation rules:

1. key MUST be known field
2. inner key MUST be allowed operator
3. inner value MUST match operator type
4. value MUST NOT be mixed
5. `in/notIn` MUST be array<int,scalar>

---

# 🟪 Generic Filter Types

| Operator      | Must Accept       |     |       |        |
|---------------|-------------------|-----|-------|--------|
| in            | `array<int,string | int | float | bool>` |
| notIn         | same as above     |     |       |        |
| contains      | string → string   |     |       |        |
| startsWith    | string → string   |     |       |        |
| gt/gte/lt/lte | numeric → numeric |     |       |        |
| exists        | bool              |     |       |        |

---

# 🧹 Filter Normalization

Repository MUST normalize filters:

### 1️⃣ Snake_case → camelCase

`created_at` → `createdAt`

### 2️⃣ Trim strings

Remove extra whitespace.

### 3️⃣ Type cast numbers

"5" → 5 (if repository expects numeric)

### 4️⃣ Normalize operators to lowercase

`GT` → `gt`

### 5️⃣ Remove null filters

`['name' => null]` → allowed only as `eq => null`.

---

# 🧩 Driver Compatibility

## ✔ MySQL + DBAL

Filters MUST be converted into normalized SQL-safe structures.
NO raw SQL allowed.

## ✔ MongoDB

Filters MUST convert cleanly to MongoDB semantics without raw operators.

Example:

```
['age' => ['gt' => 30]]
```

Becomes:

```
['age' => ['$gt' => 30]]
```

…but MUST happen internally (never accept `$gt` from user).

## ✔ Redis / Predis

Filtering allowed ONLY when repository implements filter logic in-memory.

Redis does not support server-side filtering → repository MUST filter in PHP after fetch.

---

# 🟣 Fake Driver Filtering

Fake Storage MUST behave exactly like real filtering:

* FakeMySQLAdapter uses QueryFilterTrait
* FakeMongoAdapter applies the same normalized logic
* FakeRedisAdapter filters in-memory with same semantics

Fake tests MUST confirm identical behaviors.

---

# 🧪 Testing Filtering

Tests MUST include:

### ✔ Fake Tests

* MySQL fake filter tests
* DBAL fake filter tests
* Mongo fake filter tests
* Redis fake filter tests

### ✔ Real Tests

* Test filters against real PDO
* Real DBAL
* Real Mongo collection

### ✔ Error Tests

* invalid operator
* invalid type
* invalid field
* wrong array shape
* empty in/notIn arrays
* mixed-type arrays

Filtering MUST throw:

```
RepositoryException::invalidFilter
```

---

# 🧩 Summary

Filtering layer MUST be:

* unified
* safe
* deterministic
* typed
* normalized
* portable across all drivers
* validated before execution

This specification is **final and authoritative**
and MUST NOT change except through roadmap updates.
