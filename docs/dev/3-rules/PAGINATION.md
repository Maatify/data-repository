![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# 📄 Pagination Rules  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Define the official pagination strategy for all repository methods.

Pagination MUST work identically across:

- MySQL (PDO)
- DBAL
- MongoDB
- Redis / Predis
- Fake drivers (FakeMySQL, FakeDBAL, FakeMongo, FakeRedis)

Using the unified `PaginationDTO` from `maatify/common`.

---

# 📚 Table of Contents

1. [Pagination Philosophy](#pagination-philosophy)  
2. [PaginationDTO Requirements](#paginationdto-requirements)  
3. [Pagination Fields](#pagination-fields)  
4. [MUST Rules](#must-rules)  
5. [MUST NOT Rules](#must-not-rules)  
6. [Normalization Rules](#normalization-rules)  
7. [Pagination Flow](#pagination-flow)  
8. [Driver-Specific Behavior](#driver-specific-behavior)  
9. [Fake Driver Pagination](#fake-driver-pagination)  
10. [Testing Pagination](#testing-pagination)  
11. [Summary](#summary)

---

# 🧠 Pagination Philosophy

Pagination must be:

- **Unified:** نفس الـ DTO ونفس القواعد لكل السائقين  
- **Predictable:** page/limit → offset rules ثابتة  
- **Typed:** no mixed/no untyped arrays  
- **Deterministic:** نفس النتائج في fake و real  
- **Safe:** ممنوع يسمح بأي raw limit/offset من الخارج  

Pagination is ALWAYS handled at the repository layer,  
NEVER at the driver or hydrator level.

---

# 🟦 PaginationDTO Requirements

The repository MUST accept ONLY:

```

\ Maatify\Common\Pagination\PaginationDTO

```

DTO MUST contain:

| Field   | Type                | Required | Description            |
|---------|---------------------|----------|------------------------|
| page    | int                 | YES      | Always >= 1            |
| limit   | int                 | YES      | Always >= 1            |
| sortBy  | ?string             | NO       | Optional sorting field |
| sortDir | ?string             | NO       | asc / desc             |
| filters | array<string,mixed> | NO       | optional filtering     |

---

# 🟩 Pagination Fields

## Page
- MUST be ≥ 1  
- If page = 1 → offset = 0  
- No negative values allowed  

## Limit
- MUST be ≥ 1  
- MUST NOT exceed repository-defined MAX_LIMIT (default 100)  

## Offset Formula
```

offset = (page - 1) * limit

```

---

# 🟧 MUST Rules

Pagination MUST:

1. Validate DTO before ANY driver execution  
2. Normalize sorting (case-insensitive)  
3. Reject invalid sort direction  
4. Reject invalid sortBy fields  
5. Apply limit/offset AFTER filtering  
6. Apply sorting BEFORE limit/offset  
7. Apply filtering BEFORE sorting  

Order of operations:

```

1. fetch dataset
2. filter (if any)
3. sort (if any)
4. paginate results (limit + offset)
5. hydrate DTOs

```

---

# 🟥 MUST NOT Rules

Pagination MUST NOT:

❌ Accept raw SQL (e.g., "ORDER BY id DESC")  
❌ Accept negative page/limit  
❌ Accept non-typed arrays  
❌ Accept unknown sorting fields  
❌ Do pagination inside drivers  
❌ Allow unlimited results  
❌ Allow page = 0 or limit = 0  
❌ Depend on database functions  

---

# 🧹 Normalization Rules

### 1. Sorting normalization
```

ASC → asc
DESC → desc
Asc → asc

```

### 2. Validate sort field exists in schema  
Repository MUST define allowed sort fields.

### 3. Numeric normalization
Strings that contain numbers → cast to numbers.

### 4. Filters normalization  
Filters MUST go through repository filter validators  
(see FILTERING.md).

---

# 🔄 Pagination Flow

The internal flow MUST be:

```

validate PaginationDTO
extract: page, limit, sortBy, sortDir, filters

driverData = fetchFromDriver(filters)

driverData = applyFiltering(driverData)

if (sortBy)
driverData = sort(driverData, sortBy, sortDir)

paginated = slice(driverData, offset, limit)

return hydrate(paginated)

```

---

# 🧩 Driver-Specific Behavior

## ✔ MySQL / PDO

Repository MUST generate:

```

LIMIT :limit OFFSET :offset
ORDER BY column ASC|DESC (if sort enabled)

```

But MUST validate:

- column exists  
- sorting direction valid  

NO raw SQL strings allowed from outside.

---

## ✔ DBAL

Repository MUST use:

```

->setMaxResults(limit)
->setFirstResult(offset)
->orderBy(sortBy, sortDir)

```

---

## ✔ MongoDB

Mongo queries MUST use:

- `skip(offset)`  
- `limit(limit)`  
- `sort([field => 1 or -1])`

Never accept raw `$sort` array from outside.

---

## ✔ Redis / Predis

Redis does not support server-side pagination.  
Repository MUST:

- fetch all keys/objects  
- filter  
- sort  
- slice manually  

Fake Redis must match EXACT behavior.

---

# 🟣 Fake Driver Pagination

FakeMySQL + FakeDBAL + FakeMongo MUST:

- apply sorting before slicing  
- apply filtering before sorting  
- use QueryFilterTrait rules  
- match output of real drivers EXACTLY  
- return deterministic, stable results  

Fake tests MUST confirm equality with real tests.

---

# 🧪 Testing Pagination

Tests MUST verify:

### ✔ Fake tests
- pagination on FakeMySQL  
- pagination on FakeDBAL  
- pagination on FakeMongo  
- pagination on FakeRedis  

### ✔ Real tests
- pagination on PDO  
- pagination on DBAL  
- pagination on MongoDB  

### ✔ Error cases
- invalid page  
- invalid limit  
- limit > MAX_LIMIT  
- invalid sort field  
- invalid sort direction  
- pagination combined with filters  
- pagination combined with hydration  

---

# 🧩 Summary

Pagination layer MUST be:

- unified  
- type-safe  
- deterministic  
- normalized  
- portable across drivers  
- identical between real and fake drivers  

Pagination MUST be applied in the correct deterministic order  
and MUST ALWAYS use the official PaginationDTO.

This spec MUST NOT change except through roadmap updates.
