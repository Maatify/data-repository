# Maatify Data Repository

**Unified repository abstraction layer normalizing MySQL, MongoDB, and Redis real drivers and fake drivers.**

[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg?style=for-the-badge)](https://www.php.net/)

> **Tip:** For detailed documentation, see [docs/README.full.md](docs/README.full.md).

---

## 🚀 Overview

This library separates **domain logic** from **database drivers**. It guarantees that your code behaves **exactly the same** whether you use:

*   **Real Adapters** (`maatify/data-adapters`): Connect to real MySQL, Redis, or Mongo databases.
*   **Fake Adapters** (`maatify/data-fakes`): Use in-memory arrays for fast, reliable unit tests.

### Supported Drivers

| Type | Real Driver | Fake Driver |
| :--- | :--- | :--- |
| **MySQL** | `PDO` / `Doctrine DBAL` | In-Memory Array (SQL-like) |
| **MongoDB** | `mongodb/mongodb` | In-Memory Collection (BSON-like) |
| **Redis** | `redis` / `predis` | In-Memory Key-Value Store |

---

## 📦 Installation

```bash
composer require maatify/data-repository
```

---

## ⚡ Quick Usage

### 1. Create a Repository

Extend the base class for your database type (`MySQL`, `Mongo`, or `Redis`):

```php
use Maatify\DataRepository\Base\BaseMySQLRepository;

class UserRepository extends BaseMySQLRepository
{
    protected string $tableName = 'users';
}
```

### 2. Use it (Production)

Inject a **Real Adapter** (via `maatify/data-adapters`):

```php
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;

$config = new EnvironmentConfig(__DIR__);
$resolver = new DatabaseResolver($config);

// Connect to Real MySQL
$adapter = $resolver->resolve('mysql.main');
$repo = new UserRepository($adapter);

// Use Generic CRUD
$users = $repo->findBy(['active' => 1], ['created_at' => 'DESC']);
```

### 3. Test it (CI / Unit Tests)

Inject a **Fake Adapter** (via `maatify/data-fakes`):

```php
use Maatify\DataFakes\Adapters\MySQL\FakeMySQLAdapter;
use Maatify\DataFakes\Storage\FakeStorageLayer;

$storage = new FakeStorageLayer();
$adapter = new FakeMySQLAdapter($storage);

// Seed fake data
$storage->seed('users', [
    ['id' => 1, 'active' => 1, 'name' => 'Alice'],
]);

$repo = new UserRepository($adapter);
$users = $repo->findBy(['active' => 1]); // Returns Alice
```

---

## 🧩 Key Features

*   **Generic CRUD**: `find`, `findBy`, `findOneBy`, `insert`, `update`, `delete`, `count`, `paginate`.
*   **Advanced Filtering**: `IN`, `LIKE`, ranges (`>`, `<`), `IS NULL`.
*   **Sorting**: Multi-column `orderBy` normalized across drivers.
*   **Pagination**: Standardized `paginate()` with `PaginationResultDTO`.
*   **Hydration**: Transform arrays to DTOs via `HydratorInterface`.
*   **Strict Validation**: Prevents invalid offsets, limits, or types.

---

## 📄 Documentation

*   [**Full Documentation**](docs/README.full.md)
*   [**Phase 1: Bootstrap**](docs/phases/README.phase1.md)
*   [**Phase 3: Generic CRUD**](docs/phases/README.phase3.md)
*   [**Phase 15: Pagination**](docs/phases/README.phase15.md)
*   [**Phase 16: Pagination Optimization**](docs/phases/README.phase16.md)

---

## 📄 License

MIT License © 2025 Maatify.dev
