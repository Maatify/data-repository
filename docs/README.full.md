# **Maatify Data Repository** – Full Documentation

[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)


> This is the extended full documentation for the Maatify Data Repository.  
> For the short version, see the main [`README.md`](../README.md).

**Unified repository abstraction layer normalizing MySQL, MongoDB, and Redis real drivers and fake drivers.**

This library is part of the **Maatify Ecosystem**, providing a strict, type-safe repository pattern that isolates domain logic from database drivers. It guarantees **identical behavior** across:

* Real databases via `maatify/data-adapters`
* In-memory fake drivers via `maatify/data-fakes`

Perfect for production AND deterministic testing.

---

## 📘 Table of Contents
- Features
- Installation
- Usage
- Wiring with Adapters
- Testing
- Architecture
- Roadmap
- Phase Documentation
- Quick Start
- Testing Philosophy
- License
- Author

---

## 🚀 Features

* **Driver Normalization**
    * **MySQL** → PDO / Doctrine DBAL
    * **Redis** → PhpRedis / Predis
    * **MongoDB** → MongoDB\Client / MongoDB\Database
* **Strict Architecture**
    * Enforces `RepositoryInterface`
    * Validates adapter–repository compatibility
* **Ecosystem Integration**
    * `maatify/data-adapters` for real drivers
    * `maatify/data-fakes` for deterministic CI testing
    * `maatify/psr-logger` for logging
    * `maatify/bootstrap` for environment config
* **Zero native driver exposure**
  Everything uses `AdapterInterface`.

---

## 🧩 Core Concepts

- **Repositories** isolate domain logic from drivers
- **Adapters** normalize real & fake database connections
- **Resolvers** map DSN → driver instance
- **Hydration** (Phase 4) converts arrays to DTOs
- **Symmetry Guarantee**: fake + real behave 100% the same

---

## 📦 Installation

```bash
composer require maatify/data-repository
```

**Recommended Packages:**

```bash
composer require maatify/data-adapters
composer require maatify/bootstrap
```

---

## 🛠 Usage

### 1. Defining a Repository

#### **MySQL Example**

```php
namespace App\Repository;

use Maatify\DataRepository\Base\BaseMySQLRepository;

class UserRepository extends BaseMySQLRepository
{
    protected string $tableName = 'users';

    public function findActiveUsers(): array
    {
        // getDriver() returns raw PDO or DBAL Connection
        $stmt = $this->getDriver()
            ->prepare("SELECT * FROM {$this->tableName} WHERE active = 1");

        $stmt->execute();
        return $stmt->fetchAll();
    }
}
```

#### **Redis Example**

```php
namespace App\Repository;

use Maatify\DataRepository\Base\BaseRedisRepository;

class SessionRepository extends BaseRedisRepository
{
    public function getSession(string $id): ?string
    {
        // getDriver() returns raw Redis or Predis Client
        return $this->getDriver()->get("session:{$id}");
    }
}
```

---

### 2. Wiring with Adapters

```php
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;
use App\Repository\UserRepository;

// 1. Initialize Config & Resolver
$config = new EnvironmentConfig(__DIR__);
$resolver = new DatabaseResolver($config);

// 2. Resolve Adapter (Real Connection)
$adapter = $resolver->resolve('mysql.main', autoConnect: true);

// 3. Instantiate Repository
$userRepo = new UserRepository($adapter);

// 4. Usage
$users = $userRepo->findActiveUsers();
```

---

## 🧪 Testing

`maatify/data-fakes` allows running repository tests **without any database**.

```php
use Maatify\DataFakes\Adapters\MySQL\FakeMySQLAdapter;
use Maatify\DataFakes\Storage\FakeStorageLayer;
use PHPUnit\Framework\TestCase;
use App\Repository\UserRepository;

class UserRepositoryTest extends TestCase
{
    public function testFindActiveUsers()
    {
        // 1. Setup Fake Environment
        $storage = new FakeStorageLayer();
        $adapter = new FakeMySQLAdapter($storage);

        // 2. Seed Data
        $storage->seed('users', [
            ['id' => 1, 'active' => 1],
            ['id' => 2, 'active' => 0],
        ]);

        // 3. Execute Repository Logic
        $repo = new UserRepository($adapter);
        $results = $repo->findActiveUsers();

        // 4. Assert
        $this->assertCount(1, $results);
        $this->assertEquals(1, $results[0]['id']);
    }
}
```

---

## 🏗 Architecture Overview

### Layer Hierarchy
1.  **Application Layer**: Consumes Repositories.
2.  **Repository Layer** (`maatify/data-repository`):
    *   Abstracts query logic.
    *   Validates adapter types.
    *   Provides standardized Exception handling.
3.  **Adapter Layer** (`maatify/data-adapters` or `maatify/data-fakes`):
    *   Wraps the raw driver.
    *   Implements `AdapterInterface`.
4.  **Driver Layer**:
    *   Native PHP extensions (`PDO`, `redis`, `mongodb`).
    *   Libraries (`doctrine/dbal`, `predis/predis`).

```
src/
├── Exceptions/
│   └── RepositoryException.php
│
├── Logging/
│   └── RepositoryLogger.php
│
├── Resolver/
│   └── RepositoryResolver.php
│
├── Base/
│   ├── BaseRepository.php
│   ├── BaseMySQLRepository.php
│   ├── BaseMongoRepository.php
│   └── BaseRedisRepository.php
│
├── Generic/          # Phase 3 — Generic CRUD Repositories
│   ├── GenericMongoRepository.php
│   ├── GenericMySQLRepository.php
│   ├── GenericRedisRepository.php
│   └── Support/
│       ├── FilterUtils.php  # Phase 4 — Advanced Filtering
│       ├── MysqlOps.php
│       ├── MongoOps.php
│       └── RedisOps.php
│
└── Hydration/        # Phase 11 — DTO Mapping & Object Hydration

```

---

## 📅 Roadmap & Phase Status

> The executor/roadmap engine drives this library in phases. The table below reflects the **current repository state**.

| Phase | Module                                      | Status         | Notes                       |
|-------|---------------------------------------------|----------------|-----------------------------|
| 1     | Bootstrap + Resolver + Exceptions           | ✅ Completed    | Project foundation ready    |
| 2     | Base Repositories (MySQL / Redis / Mongo)   | ✅ Completed    | Drivers unified             |
| 3     | CRUD Layer + Basic Filtering                | ✅ Completed    | Implementing CRUD + filters |
| 4     | Advanced Filtering (IN, LIKE, Ranges)       | ✅ Completed    | SQL & Mongo                 |
| 5     | Ordering & Sorting                          | ⏳ Pending      | Next Phase                  |

---

## 📚 Development Phases & Documentation Links

- **Phase 1 — Project Bootstrap & Core Architecture**  
  [`phases/README.phase1.md`](phases/README.phase1.md)

- **Phase 2 — Base Repository Layer**  
  [`phases/README.phase2.md`](phases/README.phase2.md)

- **Phase 3 — Generic Repository Implementations**  
  [`phases/README.phase3.md`](phases/README.phase3.md)

- **Phase 4 — Advanced Filtering**
  [`phases/README.phase4.md`](phases/README.phase4.md)

---

## 📚 Modern Quick Start (Phase 3+)

```php
use Maatify\DataRepository\Resolver\RepositoryResolver;
use Maatify\Bootstrap\Core\Bootstrap;

Bootstrap::init();

$users = RepositoryResolver::resolve('mysql://users');
$user  = $users->find(42);

$cache = RepositoryResolver::resolve('redis://session:');
$cache->set('theme', 'dark', 3600);

$logs = RepositoryResolver::resolve('mongo://app_logs');
```

---

## 🧪 Testing Philosophy

* **Fake tests** → deterministic, isolated, CI-friendly
* **Real tests** → validate real driver behavior
* **Full symmetry** guaranteed by the Fake Driver layer

```bash
composer test
composer stan
```

---

## 📄 License

**MIT License** © 2025 Maatify.dev

---

## 👤 Author

**Mohamed Abdulalim** ([@megyptm](https://github.com/megyptm))
[https://www.maatify.dev](https://www.maatify.dev)

---

<p align="center">
  <sub style="color:#777">Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a> — Unified Ecosystem for Modern PHP Libraries</sub>
</p>
