![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 🧱 DTO Rules (Data Transfer Objects Specification)  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Define the official rules for all DTO classes used across the repository layer.

DTOs serve as **typed, immutable, predictable** data containers  
returned by repositories after filtering, validation, and hydration.

This document ensures:

- unified DTO structure  
- deterministic hydration behavior  
- strict type-safety  
- no hidden logic in DTOs  
- compatibility with Fake and Real driver outputs  

---

# 📚 Table of Contents

1. [What is a DTO?](#what-is-a-dto)  
2. [DTO Responsibilities](#dto-responsibilities)  
3. [MUST Rules](#must-rules)  
4. [MUST NOT Rules](#must-not-rules)  
5. [DTO Structure](#dto-structure)  
6. [Allowed Patterns](#allowed-patterns)  
7. [Forbidden Patterns](#forbidden-patterns)  
8. [Hydration Compatibility](#hydration-compatibility)  
9. [Property Naming Rules](#property-naming-rules)  
10. [Constructor Rules](#constructor-rules)  
11. [Type System Rules](#type-system-rules)  
12. [Immutability Rules](#immutability-rules)  
13. [Example DTOs](#example-dtos)  
14. [Testing DTOs](#testing-dtos)  
15. [Summary](#summary)

---

# 🟦 What is a DTO?

A **Data Transfer Object** is a simple, typed PHP object  
that acts as the output of repository methods after hydration.

DTOs MUST NOT contain:

- database logic  
- validation logic  
- filtering  
- business rules  
- side effects  

DTOs exist ONLY to hold structured data coming from the repository.

---

# 🟩 DTO Responsibilities

A DTO MUST:

✔ Hold typed data  
✔ Represent a single row/document/entity  
✔ Contain no business logic  
✔ Be compatible with array→object hydration  
✔ Be safe for serialization  
✔ Be fully deterministic  

---

# 🟧 MUST Rules

A DTO **MUST**:

1. Use **strict types**  
2. Declare **public typed properties** OR readonly constructor properties  
3. Have **zero side effects**  
4. Be compatible with HydratorInterface  
5. Accept `array<string,mixed>` hydration  
6. Support missing keys (hydrator fills defaults)  
7. Avoid nullable types unless required  
8. Contain only scalar/DTO/nested typed fields  
9. Represent exactly one domain record  
10. Prefer *readonly* for immutability (PHP 8.1+)  

---

# 🟥 MUST NOT Rules

A DTO **MUST NOT**:

❌ Perform I/O (DB, Redis, FS, HTTP)  
❌ Depend on adapters, resolvers, or repositories  
❌ Contain business logic  
❌ Modify global state  
❌ Lazy-load other DTOs  
❌ Contain methods other than simple accessors  
❌ Throw exceptions inside its constructor  
❌ Have dynamic properties  
❌ Use mixed or array without generics  
❌ Embed validation or filtering inside it  

---

# 🟫 DTO Structure

## Allowed structure (Option 1: public properties)

```php
final class UserDTO
{
    public int $id;
    public string $name;
    public string $email;
}
````

## Allowed structure (Option 2: readonly properties)

```php
final readonly class UserDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email
    ) {}
}
```

## Allowed structure (Option 3: hybrid)

```php
class OrderDTO
{
    public int $id;
    public float $total;

    public function __construct(
        public string $status
    ) {}
}
```

---

# 🟦 Allowed Patterns

### ✔ Simple scalar properties

`int`, `string`, `float`, `bool`

### ✔ Nullable scalars

`?string`, `?int`

### ✔ Nested DTOs

Allowed if hydrated explicitly.

### ✔ Arrays with generics

`array<int,AddressDTO>`
`array<string,string>`

### ✔ Readonly DTOs (recommended)

---

# 🚫 Forbidden Patterns

❌ Properties without types
❌ `mixed` type
❌ `array` without generics
❌ Static properties
❌ Constructors with side-effects
❌ Database/Redis/Mongo operations
❌ Calling adapter or resolver
❌ Storing adapters inside DTO
❌ Circular references
❌ setX()/getX() Java-style if unnecessary

DTOs MUST stay extremely lightweight.

---

# 🧭 Hydration Compatibility

HydratorInterface MUST be able to hydrate any DTO if:

✔ All constructor args have matching keys
✔ All public properties are typed
✔ Missing values have defaults or nullability
✔ Snake_case keys map to camelCase automatically

Example:

Database column → DTO property:

```
created_at → $createdAt
product_name → $productName
```

Hydrator MUST NOT run if:

* DTO requires complex transformation
* DTO constructor throws
* DTO property types mismatch driver output

---

# 🔤 Property Naming Rules

### Repository outputs snake_case

DTO properties MUST be camelCase:

```
product_name  → $productName
user_id       → $userId
created_at    → $createdAt
```

Hydrator normalizes automatically.

---

# 🔐 Constructor Rules

### Allowed:

✔ simple assignments
✔ type enforcement
✔ default values

### Forbidden:

❌ validations
❌ side effects
❌ database/redis interactions
❌ throwing exceptions for missing fields

---

# 🧬 Type System Rules

DTOs MUST use:

* union types sparingly
* nullable types only when required
* typed arrays with generics
* scalar or DTO-only properties

Forbidden:

* callable
* resource
* iterable
* stdClass

---

# 🧊 Immutability Rules

DTOs MAY be:

### readonly (preferred)

or

### mutable public properties

But MUST NOT have internally mutated state
after hydration.

---

# 🧪 Testing DTOs

DTO tests MUST verify:

✔ constructor assignment
✔ hydration compatibility
✔ strict property typing
✔ default values work
✔ converting snake_case → camelCase
✔ no business logic in DTO
✔ no side effects

---

# 🧩 Summary

This file defines the **official DTO contract** for the repository layer:

* DTOs are simple typed containers
* No logic
* No I/O
* Hydration-compatible
* Consistent naming conventions
* Safe types
* Deterministic behavior
* Fully compatible with Fake + Real drivers

This specification MUST NOT change without roadmap approval.
