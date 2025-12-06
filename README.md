# Maatify Data Repository

**Unified repository abstraction layer normalizing MySQL, MongoDB, and Redis real & fake drivers.**

![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---

[![Version](https://img.shields.io/packagist/v/maatify/data-repository?label=Version&color=4C1)](https://packagist.org/packages/maatify/data-repository)
[![PHP](https://img.shields.io/packagist/php-v/maatify/data-repository?label=PHP&color=777BB3)](https://packagist.org/packages/maatify/data-repository)
![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-blue)

[![Build](https://github.com/Maatify/data-repository/actions/workflows/test.yml/badge.svg?label=Build&color=brightgreen)](https://github.com/Maatify/data-repository/actions/workflows/test.yml)

![Monthly Downloads](https://img.shields.io/packagist/dm/maatify/data-repository?label=Monthly%20Downloads&color=00A8E8)
![Total Downloads](https://img.shields.io/packagist/dt/maatify/data-repository?label=Total%20Downloads&color=2AA9E0)

![Stars](https://img.shields.io/github/stars/Maatify/data-repository?label=Stars&color=FFD43B)
[![License](https://img.shields.io/github/license/Maatify/data-repository?label=License&color=blueviolet)](LICENSE)
![Status](https://img.shields.io/badge/Status-Stable-success)
[![Code Quality](https://img.shields.io/codefactor/grade/github/Maatify/data-repository/main?color=brightgreen)](https://www.codefactor.io/repository/github/Maatify/data-repository)

![PHPStan](https://img.shields.io/badge/PHPStan-Level%20Max-4E8CAE)
![Coverage](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/Maatify/data-repository/badges/coverage.json)

[![Changelog](https://img.shields.io/badge/Changelog-View-blue)](CHANGELOG.md)
[![Security](https://img.shields.io/badge/Security-Policy-important)](SECURITY.md)
---

# 🚀 Overview

**Maatify Data Repository** separates domain logic from database-specific implementations and provides a unified API supporting:

- **Real Drivers** (`maatify/data-adapters`)
- **Fake Drivers** (`maatify/data-fakes`)

### Why this library?

- Zero driver lock-in  
- Deterministic testing without Docker  
- Static analysis with PHPStan Level Max  
- Unified CRUD/Filters/Pagination across MySQL, MongoDB, and Redis  

### Supported Drivers

| Type     | Real Drivers                   | Fake Drivers                         |
|----------|--------------------------------|--------------------------------------|
| MySQL    | PDO / Doctrine DBAL            | In-memory SQL-like tables            |
| MongoDB  | mongodb/mongodb                | In-memory BSON-like collections      |
| Redis    | redis / predis                 | In-memory key-value store            |

---

# 📦 Installation

```bash
composer require maatify/data-repository
````

---

# ⚡ Quick Usage

## 1) Create a Repository

```php
use Maatify\DataRepository\Base\BaseMySQLRepository;

/** @extends BaseMySQLRepository<array> */
class UserRepository extends BaseMySQLRepository
{
    protected string $tableName = 'users';
}
```

## 2) Use in Production (Real Adapter)

```php
$resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));
$adapter  = $resolver->resolve('mysql.main');

$repo = new UserRepository($adapter);
$users = $repo->findBy(['active' => 1]);
```

## 3) Use in Testing (Fake Adapter)

```php
$storage = new FakeStorageLayer();
$adapter = new FakeMySQLAdapter($storage);

$storage->seed('users', [
    ['id' => 1, 'active' => 1, 'name' => 'Alice'],
]);

$repo = new UserRepository($adapter);
$repo->findBy(['active' => 1]); // Alice
```

---

# 💎 Hydration & DTOs

```php
class UserDto {
    public int $id;
    public string $name;
}

/** @extends BaseHydrator<UserDto> */
class UserHydrator extends BaseHydrator {}

$repo->setHydrator(new UserHydrator());
$user = $repo->findObject(1);
```

---

# 🧩 Key Features

*   **Generic CRUD**: `find`, `findBy`, `findOneBy`, `insert`, `update`, `delete`, `count`, `paginate`.
*   **Advanced Filtering**: `IN`, `LIKE`, ranges (`>`, `<`), `IS NULL`.
*   **Sorting**: Multi-column `orderBy` normalized across drivers.
*   **Pagination**: Standardized `paginate()` with `PaginationResultDTO`.
*   **Hydration**: Transform arrays to DTOs via `HydratorInterface`.
*   **Strict Validation**: Prevents invalid offsets, limits, or types.
*   **Static Analysis**: Fully Generic-aware (`@template T`) for PHPStan Level Max.

---

# 📄 Documentation

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

# 📚 Development History & Phase Details

<details>
<summary><strong>Click to expand</strong></summary>

This library evolves through a strict phase-based roadmap.

### Major Completed Phases

* Phase 1 – Bootstrap & Foundation
* Phase 3 – Generic CRUD
* Phase 15–17 – Pagination Improvements & Hydration
* Phase 20 – SQL & Filter Enhancements
* Phase 21 – Architecture Decoupling
* Phase 22 – FilterParser Extraction
* Phase 26 – Public API Tightening
* Phase 28 – PHPStan Generics
* Phase 29 – Developer Experience

Full details available in `docs/phases/`.

</details>

---

# 🧱 Dependencies Overview

`maatify/data-repository` relies on Maatify core ecosystem + selected open-source libraries.

---

## 🧩 Maatify Ecosystem Dependencies

| Package                   | Description                              | Role                                    |
|---------------------------|------------------------------------------|-----------------------------------------|
| **maatify/bootstrap**     | Environment loader, diagnostics, helpers | Powers `.env` and adapter bootstrapping |
| **maatify/data-adapters** | Real MySQL/Mongo/Redis adapters          | Production database connectivity        |
| **maatify/data-fakes**    | Fake in-memory drivers                   | Deterministic, Docker-free testing      |

---

## 🔌 Direct Open-Source Dependencies

| Library                    | Purpose                |
|----------------------------|------------------------|
| psr/log                    | Logging interface      |
| phpunit/phpunit            | Test suite             |
| phpstan/phpstan            | Static analysis        |
| mongodb/mongodb            | MongoDB driver         |
| predis/predis / php-redis  | Redis driver           |
| doctrine/dbal *(optional)* | MySQL DBAL abstraction |

---

## 🔄 Indirect Dependencies (via bootstrap)

| Library          | Purpose          |
|------------------|------------------|
| vlucas/phpdotenv | `.env` loader    |
| psr/container    | DI compatibility |

> Special thanks to the maintainers of these open-source libraries
> for providing the stable foundations that make this project possible. ❤️
---

# 🧪 Testing

```bash
composer test
```

Runs:

* Real vs Fake consistency checks
* Filter/Order parser tests
* Pagination & Hydration tests
* Architecture tests
* Coverage with Clover output

---


## 🪪 License

**[MIT License](LICENSE)**  
© [Maatify.dev](https://www.maatify.dev) — Free to use, modify, and distribute with attribution.

---

## 👤 Author

Engineered by **Mohamed Abdulalim** ([@megyptm](https://github.com/megyptm))  
Backend Lead & Technical Architect — https://www.maatify.dev

---

## 🤝 Contributors

Special thanks to the Maatify.dev engineering team and all open-source contributors.  
Your efforts help make this repository stronger and more reliable.

Contributions are always welcome!  
Before opening a Pull Request, please make sure to read our  
[Contributing Guide](CONTRIBUTING.md) and [Code of Conduct](CODE_OF_CONDUCT.md).

---

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a> — Unified Ecosystem for Modern PHP Libraries</sub>
</p>
