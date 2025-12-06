# Maatify Data Repository

**Unified repository abstraction layer normalizing MySQL, MongoDB, and Redis real drivers and fake drivers.**

![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---

[![Version](https://img.shields.io/packagist/v/maatify/data-repository?label=Version&color=4C1)](https://packagist.org/packages/maatify/data-repository)
[![PHP](https://img.shields.io/packagist/php-v/maatify/data-repository?label=PHP&color=777BB3)](https://packagist.org/packages/maatify/data-repository)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-blue)](https://www.php.net/)

[![Build](https://github.com/Maatify/data-repository/actions/workflows/test.yml/badge.svg?label=Build&color=brightgreen)](https://github.com/Maatify/data-repository/actions/workflows/test.yml)

[![Monthly Downloads](https://img.shields.io/packagist/dm/maatify/data-repository?label=Monthly%20Downloads&color=00A8E8)](https://packagist.org/packages/maatify/data-repository)
[![Total Downloads](https://img.shields.io/packagist/dt/maatify/data-repository?label=Total%20Downloads&color=2AA9E0)](https://packagist.org/packages/maatify/data-repository)

[![Stars](https://img.shields.io/github/stars/Maatify/data-repository?label=Stars&color=FFD43B&cacheSeconds=3600)](https://github.com/Maatify/data-repository/stargazers)
[![License](https://img.shields.io/github/license/Maatify/data-repository?label=License&color=blueviolet)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Stable-success?style=flat-square)]()
[![Code Quality](https://img.shields.io/codefactor/grade/github/Maatify/data-repository/main?color=brightgreen)](https://www.codefactor.io/repository/github/Maatify/data-repository)

[![PHPStan](https://img.shields.io/badge/PHPStan-Level%20Max-4E8CAE)](https://phpstan.org/)
[![Coverage](https://img.shields.io/badge/Coverage-92%25-9C27B0)](#)

[![Changelog](https://img.shields.io/badge/Changelog-View-blue)](CHANGELOG.md)
[![Security](https://img.shields.io/badge/Security-Policy-important)](SECURITY.md)

---

## 🚀 Overview

**Maatify Data Repository** isolates your **domain logic** from **database drivers**. It guarantees that your code behaves **exactly the same** whether you use:

*   **Real Adapters** (`maatify/data-adapters`): Connect to production MySQL, Redis, or Mongo databases.
*   **Fake Adapters** (`maatify/data-fakes`): Use in-memory arrays for fast, reliable unit tests.

### Why use this?
*   **Zero Lock-in**: Switch between drivers or mock them instantly.
*   **Deterministic Testing**: Run your entire repository layer in memory without Docker or mocks.
*   **Strict Typing**: Full PHP 8.4+ support with PHPStan Level Max generics.

### Supported Drivers

| Type        | Real Driver             | Fake Driver                      |
|:------------|:------------------------|:---------------------------------|
| **MySQL**   | `PDO` / `Doctrine DBAL` | In-Memory Array (SQL-like)       |
| **MongoDB** | `mongodb/mongodb`       | In-Memory Collection (BSON-like) |
| **Redis**   | `redis` / `predis`      | In-Memory Key-Value Store        |

---

## 📦 Installation

```bash
composer require maatify/data-repository
```

---

## ⚡ Quick Usage

### 1. Create a Repository

Extend the base class for your database type (`MySQL`, `Mongo`, or `Redis`).
Use `@extends` to define your return types for static analysis.

```php
use Maatify\DataRepository\Base\BaseMySQLRepository;

/**
 * @extends BaseMySQLRepository<array>
 */
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

## 💎 Advanced: Hydration & DTOs

Automatically transform database arrays into rich PHP Objects/DTOs.

```php
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Hydration\BaseHydrator;

// 1. Define DTO
class UserDto { public int $id; public string $name; }

// 2. Define Hydrator
/** @extends BaseHydrator<UserDto> */
class UserHydrator extends BaseHydrator { ... }

// 3. Define Repository
/** @extends GenericMySQLRepository<UserDto> */
class UserRepository extends GenericMySQLRepository
{
    protected string $tableName = 'users';
}

// 4. Usage
$repo->setHydrator(new UserHydrator());
$user = $repo->findObject(1); // Returns UserDto|null
```

---

## 🧩 Key Features

*   **Generic CRUD**: `find`, `findBy`, `findOneBy`, `insert`, `update`, `delete`, `count`, `paginate`.
*   **Advanced Filtering**: `IN`, `LIKE`, ranges (`>`, `<`), `IS NULL`.
*   **Sorting**: Multi-column `orderBy` normalized across drivers.
*   **Pagination**: Standardized `paginate()` with `PaginationResultDTO`.
*   **Hydration**: Transform arrays to DTOs via `HydratorInterface`.
*   **Strict Validation**: Prevents invalid offsets, limits, or types.
*   **Static Analysis**: Fully Generic-aware (`@template T`) for PHPStan Level Max.

---

## 📄 Documentation

*   [**New Developer Onboarding**](docs/ONBOARDING.md)
*   [**Full Documentation**](docs/README.full.md)
*   [**Practical Usage Guide (Examples)**](examples/Examples.md)

<details>
<summary><strong>📚 Development History & Phase Details</strong></summary>

The development of this library follows a strict phase-based roadmap.

*   [**Phase 1: Bootstrap**](docs/phases/README.phase1.md)
*   [**Phase 3: Generic CRUD**](docs/phases/README.phase3.md)
*   [**Phase 15: Pagination**](docs/phases/README.phase15.md)
*   [**Phase 16: Pagination Optimization**](docs/phases/README.phase16.md)
*   [**Phase 17: Hydrated Pagination**](docs/phases/README.phase17.md)
*   [**Phase 20: SQL & Filter Improvements**](docs/phases/README.phase20.md)
*   [**Phase 21: Architecture Decoupling**](docs/phases/README.phase21.md)
*   [**Phase 22: FilterParser Extraction**](docs/phases/README.phase22.md)
*   [**Phase 26: Public API Tightening**](docs/phases/README.phase26.md)
*   [**Phase 28: PHPStan Generics**](docs/phases/README.phase28.md)
*   [**Phase 29: Developer Experience**](docs/phases/README.phase29.md)

</details>

---

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) and [Code of Conduct](CODE_OF_CONDUCT.md) before submitting a Pull Request.

---

## 🪪 License

**[MIT License](LICENSE)**  
© [Maatify.dev](https://www.maatify.dev) — Free to use, modify, and distribute with attribution.

---

## 👤 Author

Engineered by **Mohamed Abdulalim** ([@megyptm](https://github.com/megyptm))  
Backend Lead & Technical Architect — https://www.maatify.dev

---

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a> — Unified Ecosystem for Modern PHP Libraries</sub>
</p>
