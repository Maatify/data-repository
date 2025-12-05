# Developer Onboarding Guide

[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

Welcome to the **Maatify Data Repository**!

This guide is designed to help you get up to speed with the architecture, core concepts, and daily workflows of the `maatify/data-repository` library.

---

## 1. What is this library?

`maatify/data-repository` is a unified repository abstraction layer that sits between your domain logic and the database drivers.

**Key Goals:**
*   **Driver Agnostic:** Write your logic once, run it against MySQL, Mongo, Redis, or In-Memory Fakes.
*   **Strict Typing:** leverages PHP 8.1+ features and generic templates (`@template T`) for static analysis.
*   **Identical Behavior:** Fake drivers behave *exactly* like real drivers (including filters, sorting, and pagination).

---

## 2. Directory Structure

The project follows a standard PSR-4 structure:

*   `src/Base/` - Abstract base classes for each driver type (`BaseMySQLRepository`, etc.).
*   `src/Generic/` - Concrete implementations of CRUD, filtering, and ordering logic.
*   `src/Hydration/` - Logic for transforming raw database arrays into DTOs.
*   `src/Pagination/` - DTOs and collections for handling paginated results.
*   `tests/` - Comprehensive test suite (Fake, Real, Integration, Architecture).
*   `examples/` - Practical usage examples for each phase of development.

---

## 3. Core Workflow

### A. Defining a Repository

You typically extend `GenericMySQLRepository` (or Mongo/Redis equivalents).

```php
/**
 * @template T of UserDTO
 * @extends GenericMySQLRepository<UserDTO>
 */
class UserRepository extends GenericMySQLRepository
{
    // ...
}
```

### B. Dependency Injection

Repositories depend on `AdapterInterface` and optionally `LoggerInterface`.

```php
$adapter = $dbResolver->resolve('mysql.main');
$repo = new UserRepository($adapter);
```

### C. Hydration (Optional but Recommended)

Attach a Hydrator to convert arrays to objects automatically.

```php
$repo->setHydrator(new UserHydrator());
$userDto = $repo->findObject(123); // Returns UserDTO|null
```

---

## 4. Querying Data

We provide a unified API for querying across all drivers.

### Filtering
Use key-value pairs. Arrays imply complex operators.

*   `['status' => 1]` (Equality)
*   `['age' => ['>=' => 18]]` (Range)
*   `['role' => ['IN' => [1, 2]]]` (Inclusion)

### Ordering
*   `['created_at' => 'DESC']`

### Pagination
*   `paginateBy($filters, $page, $perPage)` returns a `PaginationResultDTO` or `HydratedPaginationCollection`.

---

## 5. Testing Your Code

We prioritize **Fake-First Development**.

1.  **Unit Tests:** Use `FakeMySQLAdapter` (from `maatify/data-fakes`) to test your repository logic without a database.
2.  **Integration Tests:** Verify against a real database using `MySQLAdapter`.

**Example:**
```php
$storage = new FakeStorageLayer();
$adapter = new FakeMySQLAdapter($storage);
$repo = new UserRepository($adapter);

// Seed data
$storage->seed('users', [['id' => 1, 'name' => 'Test']]);

// Test logic
$result = $repo->find(1);
```

---

## 6. Public API Surface

Key methods available on all Generic Repositories:

*   **Read:** `find()`, `findBy()`, `findOneBy()`, `findAll()`, `count()`
*   **Write:** `insert()`, `update()`, `delete()`
*   **Pagination:** `paginate()`, `paginateBy()`
*   **Hydration:** `findObject()`, `findObjectsBy()`, `paginateObjects()`, `paginateObjectsBy()`

---

## 7. Configuration

*   **Limits:** Default max limit is 10,000. Configurable via `LimitOffsetConfig`.
*   **Normalization:** Result IDs are auto-cast to int/string. Configurable via `NormalizerOptions`.

---

## 8. Where to go next?

*   Check `examples/` for code snippets.
*   Read `docs/phases/` for deep dives into specific features.
*   Explore `tests/Generic/` to see edge cases and expected behaviors.

Happy Coding!
