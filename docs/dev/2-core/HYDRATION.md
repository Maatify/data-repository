![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 💧 Hydration Layer Guide  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Define how array-to-object hydration works inside repository classes.

The Hydration Layer converts associative arrays returned from drivers  
(PHP array rows, Mongo documents, Redis hash maps, fake storage rows)  
into strongly typed PHP objects (DTOs, Entities, Records).

This document explains:

- Why hydration is needed  
- How it works  
- Where it lives in the architecture  
- How repositories use it  
- What is allowed and forbidden  

---

# 📚 Table of Contents

1. [Overview](#overview)  
2. [Why Hydration Exists](#why-hydration-exists)  
3. [HydratorInterface](#hydratorinterface)  
4. [SimpleHydrator](#simplehydrator)  
5. [Hydration Flow](#hydration-flow)  
6. [DTO Requirements](#dto-requirements)  
7. [Repository Integration](#repository-integration)  
8. [Filtering + Validation](#filtering--validation)  
9. [Fake vs Real Hydration Behavior](#fake-vs-real-hydration-behavior)  
10. [Forbidden Behaviors](#forbidden-behaviors)  
11. [Best Practices](#best-practices)

---

# 🧭 Overview

Hydration is the mechanism that turns:

```

array<string,mixed>

```

into:

```

object(SomeDTO)

````

Repositories use hydrators to provide:

- consistent return types  
- safer domain objects  
- type-checked data boundaries  
- cleaner business logic separation  

Hydration is optional per repository but highly recommended.

---

# ❓ Why Hydration Exists

Drivers return **arrays**, not objects:

| Driver           | Output               |
|------------------|----------------------|
| PDO              | associative array    |
| DBAL             | associative array    |
| MongoDB          | BSON converted array |
| Redis            | strings/integers     |
| FakeStorageLayer | raw arrays           |

Hydration exists to:

### ✔ Enforce type-safety  
### ✔ Normalize missing fields  
### ✔ Provide object behavior  
### ✔ Offer reusable data models  
### ✔ Simplify domain logic  
### ✔ Prevent array-shape assumptions  

Without hydration, repositories risk:

- missing keys  
- untrusted array shapes  
- inconsistent return values  
- hidden bugs  

---

# 🧩 HydratorInterface

The contract implemented by the hydration classes.

### MUST:

```php
interface HydratorInterface
{
    /**
     * @param class-string $class
     * @param array<string,mixed> $data
     */
    public function hydrate(string $class, array $data): object;
}
````

### Responsibilities:

* Validate the class exists
* Create object instance
* Hydrate properties
* Handle missing keys safely

### Forbidden:

* Passing through input arrays
* Mutating repository layer state
* Throwing driver exceptions

---

# 🔧 SimpleHydrator

The default hydrator implementation in this package.

## Behavior:

* Creates an object using reflection or constructor
* Maps array keys → object properties
* Ignores missing fields gracefully
* Converts snake_case → camelCase if needed
* Enforces type consistency

## Example:

```php
$user = $hydrator->hydrate(UserDTO::class, [
    'id' => 1,
    'name' => 'John'
]);
```

---

# 🔄 Hydration Flow

Typical repository hydration flow:

```
$driver → array result → filter/validate → hydrate → DTO
```

Full pipeline:

1. Repository retrieves results (array<string,mixed>)
2. Filters may clean or transform data
3. Validator may enforce rules
4. Hydrator converts to object
5. Repository returns DTO or list of DTOs

---

# 📦 DTO Requirements

DTOs must:

* Be simple objects (no business logic)
* Declare typed properties
* Optionally use readonly for immutability
* Mirror the data structure returned from drivers
* Include minimal transformation logic

### Allowed DTO structures:

```php
class UserDTO
{
    public int $id;
    public string $name;
}
```

or

```php
final readonly class OrderDTO
{
    public function __construct(
        public int $id,
        public string $status,
        public float $amount
    ) {}
}
```

---

# 🔗 Repository Integration

Repositories may:

* inject a hydrator via constructor
* override hydration logic per-repository
* return arrays OR objects depending on method

### Example integration:

```php
class UserRepository extends BaseMySQLRepository
{
    public function findUser(int $id): ?UserDTO
    {
        $row = $this->find($id);

        return $row
            ? $this->hydrator->hydrate(UserDTO::class, $row)
            : null;
    }
}
```

---

# 🔍 Filtering & Validation

Hydration usually happens **after**:

* filters
* validators
* normalization

This ensures DTOs only receive valid values.

Flow:

```
raw array → validate → filter → hydrate → DTO
```

---

# ⚖️ Fake vs Real Hydration Behavior

Hydration MUST behave identically with:

### ✔ Fake drivers

### ✔ Real drivers

FakeStorageLayer produces **arrays**
Real drivers produce **arrays**
MongoDB produces arrays after BSON conversion

Hydrator MUST NOT rely on:

* driver type
* driver origin
* test environment

Hydration must be completely independent and deterministic.

---

# ❌ Forbidden Behaviors

The Hydration Layer MUST NOT:

* depend on FakeRepository
* read from drivers directly
* call getConnection()
* bypass repository normalization
* mutate adapter state
* perform IO or queries
* depend on array shape assumptions
* accept raw invalid inputs without validation

---

# ⭐ Best Practices

* Use readonly DTOs for safer data modeling
* Keep DTO logic minimal
* Validate before hydration
* Log hydration failures using PSR-3 logger
* Allow custom hydrators per repository
* Do not hydrate for trivial scalar responses
* Keep hydration deterministic and side-effect free

---

# 🧩 Summary

The Hydration Layer is a crucial boundary:

* Protects from unsafe array structures
* Provides clean object-based return types
* Ensures consistent behavior across all drivers
* Works identically with fake + real data sources
* Simplifies domain logic dramatically

This document MUST be followed during implementation
and MUST NOT change except through roadmap updates.
