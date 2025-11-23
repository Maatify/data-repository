![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🗂 Repository Schema Specification  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Define the mandatory schema structure every repository MUST implement to ensure safe, deterministic, type-correct, and predictable operations across all drivers and environments.

A "Schema" represents the *shape* of your repository's data:
- what fields exist  
- what types they hold  
- which fields can be filtered  
- which fields can be sorted  
- which fields can be updated  
- which fields are required for insertion  
- how DTO maps to columns  

This document ensures **every repository implements the same rules**.

---

# 📚 Table of Contents

1. [Schema Philosophy](#schema-philosophy)  
2. [Schema Structure](#schema-structure)  
3. [Field Definitions](#field-definitions)  
4. [Field Types](#field-types)  
5. [Insert Schema](#insert-schema)  
6. [Update Schema](#update-schema)  
7. [Filter Schema](#filter-schema)  
8. [Sort Schema](#sort-schema)  
9. [DTO Mapping Schema](#dto-mapping-schema)  
10. [Repository Required Methods](#repository-required-methods)  
11. [Schema Validation Rules](#schema-validation-rules)  
12. [Schema Storage Rules](#schema-storage-rules)  
13. [Examples](#examples)  
14. [Summary](#summary)

---

# 🧠 Schema Philosophy

Repository schema MUST be:

- **explicit** (no dynamic schema)  
- **typed** (no mixed fields)  
- **complete** (define all fields)  
- **safe** (only allowed fields can be used)  
- **deterministic** (consistent across fake + real drivers)  
- **independent** (not read from database)  
- **static** (defined in class, not runtime)  

Repository MUST NEVER infer schema from:

❌ database structure  
❌ driver metadata  
❌ first row of results  
❌ runtime analysis  

Schema MUST be hardcoded and version-controlled.

---

# 🟦 Schema Structure

Every repository MUST provide a method:

```php
protected function schema(): array;
````

Schema MUST return an associative array:

```
[
  'fields' => [...],
  'insertable' => [...],
  'updatable' => [...],
  'filterable' => [...],
  'sortable' => [...],
  'dto' => ClassName::class,
  'primaryKey' => 'id'
]
```

---

# 🟩 Field Definitions

Each field MUST define:

```
[
  'type' => 'int|string|float|bool|array|datetime',
  'nullable' => bool,
  'default' => mixed|null,
]
```

Example:

```php
'email' => [
    'type' => 'string',
    'nullable' => false,
    'default' => null,
]
```

---

# 🟧 Field Types

Allowed types:

| Type     | Meaning                                   |
|----------|-------------------------------------------|
| int      | numeric integer                           |
| string   | text                                      |
| float    | double/decimal                            |
| bool     | boolean                                   |
| array    | typed generic array                       |
| datetime | DateTimeInterface-compatible string       |
| json     | structured JSON stored as string encoded  |
| enum     | allowed set of strings                    |
| object   | only if DTO-recursive hydration is needed |

Forbidden:

❌ mixed
❌ resource
❌ callable
❌ stdClass

---

# 🟪 Insert Schema

`insertable` MUST list:

* allowed keys
* required keys
* optional keys
* default values

Repository MUST:

* reject unknown insert keys
* apply defaults for missing values
* validate types
* validate required fields

Example:

```php
'insertable' => ['email', 'name', 'status']
```

---

# 🟫 Update Schema

`updatable` MUST list fields that can be updated.

Repository MUST:

* reject unknown fields
* validate field type
* allow partial updates
* MUST NOT enforce required fields here

Example:

```php
'updatable' => ['email', 'status']
```

---

# 🟨 Filter Schema

`filterable` MUST list fields the repository allows filtering on.

Example:

```
'filterable' => ['id', 'email', 'status', 'createdAt']
```

Repository MUST reject:

❌ filter on unknown field
❌ filter on unfilterable field
❌ filter type mismatch
❌ disallowed operators

---

# 🟦 Sort Schema

`sortable` MUST list allowed columns:

```
'sortable' => ['id', 'createdAt', 'name']
```

Repository MUST reject:

* invalid fields
* invalid sort direction

---

# 🟩 DTO Mapping Schema

`dto` MUST contain:

* fully-qualified DTO class
* DTO MUST be compliant with docs/DTO_RULES.md

Example:

```
'dto' => UserDTO::class
```

Mapping behavior:

* snake_case field names map to camelCase DTO properties
* repository MUST ensure consistent mapping

Forbidden:

❌ dynamic DTO class resolution
❌ DTO per driver
❌ database-driven mapping

---

# 🧱 Repository Required Methods

Every Repository MUST implement:

```php
protected function schema(): array;
```

And MUST use schema for:

* validation
* filtering
* sorting
* hydration
* DTO mapping
* field normalization

---

# 🔍 Schema Validation Rules

Schema MUST be validated at runtime using IntegrationValidator:

✔ all fields exist
✔ all required metadata exists
✔ no unknown keys
✔ DTO exists
✔ primaryKey exists
✔ insertable fields exist in "fields"
✔ updatable fields exist in "fields"
✔ filterable fields exist in "fields"
✔ sortable fields exist in "fields"

---

# 📦 Schema Storage Rules

Schema MUST:

* be defined inside repository class
* be static or protected array
* MUST NOT be loaded from external file
* MUST NOT be modified at runtime
* MUST NOT depend on environment
* be version-controlled

---

# 📝 Examples

Example minimal schema for `users`:

```php
protected function schema(): array
{
    return [
        'primaryKey' => 'id',
        'dto' => UserDTO::class,

        'fields' => [
            'id' => ['type' => 'int', 'nullable' => false],
            'email' => ['type' => 'string', 'nullable' => false],
            'name' => ['type' => 'string', 'nullable' => false],
            'createdAt' => ['type' => 'datetime', 'nullable' => false],
        ],

        'insertable' => ['email', 'name'],
        'updatable' => ['email', 'name'],
        'filterable' => ['id', 'email', 'name', 'createdAt'],
        'sortable' => ['id', 'createdAt']
    ];
}
```

---

# 🧩 Summary

Repository schema MUST:

* define how data looks
* control safe inputs
* determine DTO mapping
* restrict filtering and sorting
* define insert/update rules
* ensure deterministic repository behavior
* ensure fake & real drivers return compatible data

This is an authoritative file
and MUST NOT be modified except through roadmap updates.
