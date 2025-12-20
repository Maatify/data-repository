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
- [Features](#-features)
- [Core Concepts](#-core-concepts)
- [Installation](#-installation)
- [Onboarding Guide](ONBOARDING.md)
- [Practical Usage Guide](../examples/Examples.md)
- [Usage](#-usage)
- [Wiring with Adapters](#2-wiring-with-adapters)
- [Testing](#-testing)
- [Architecture](#-architecture-overview)
- [Roadmap & Status](#-roadmap--phase-status)
- [Phase Documentation](#-development-phases--documentation-links)
- [Quick Start](#-modern-quick-start-recommended)
- [Testing Philosophy](#-testing-philosophy)
- [License](#-license)
- [Author](#-author)

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
* **Advanced Features**
    * Unified Filtering (`IN`, `LIKE`, Ranges)
    * Unified Sorting (`ASC`/`DESC`, multi-column)
    * **Limits & Offsets** (Strict Validation)
    * **Pagination** (Phase 15+): Standardized `paginate` and `paginateBy` returning `PaginationResultDTO`.
    * **Result Normalization** (Unified ID mapping, Type casting)
    * **Standardized Error Handling** (Phase 8): All repositories throw `RepositoryException`
    * **Hydration Pipeline** (Phase 11+): Contracts, Context, and Base Implementations for transforming results.
    * **Paginated Hydrated Results** (Phase 17): A seamlessly hydrate paginated result sets into object collections.
    * **Static Analysis & Generics** (Phase 28): Fully typed for PHPStan (Level Max) with `@template T` support.
    * JSON column querying (MySQL)
* **Zero native driver exposure**
  Everything uses `AdapterInterface`.

---

# 🛑 MongoDB ObjectId Casting Rules

> **Important**: To ensure predictability, this library enforces strict rules for MongoDB ID casting.

- **Casting is allowed ONLY in `find(id)`**
- **24-char hex strings are cast ONLY in `find(id)`**
- `findBy`, `paginate`, and filters **NEVER** cast automatically
- Explicit `new ObjectId(...)` is required in filters

**Positive Example (Casting happens):**
```php
// Automatically converts string "507f1f77bcf86cd799439011" to ObjectId
$repo->find("507f1f77bcf86cd799439011");
```

**Negative Example (No casting happens):**
```php
// Remains a literal string "507f1f77bcf86cd799439011"
$repo->findBy(['custom_id' => "507f1f77bcf86cd799439011"]);
```

---

## 🧩 Core Concepts

- **Repositories** isolate domain logic from drivers
- **Adapters** normalize real & fake database connections
- **Resolvers** map DSN → driver instance
- **Hydration** (Phase 11+) converts arrays to DTOs
- **Pagination** (Phase 15+) standardized data paging
- **Symmetry Guarantee**: fake and real behave 100% the same

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

#### **Generics & Hydration Example (Phase 28)**

```php
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Hydration\BaseHydrator;

class UserDto { public int $id; public string $name; }

/** @extends BaseHydrator<UserDto> */
class UserHydrator extends BaseHydrator { ... }

/** @extends GenericMySQLRepository<UserDto> */
class UserRepository extends GenericMySQLRepository
{
    protected string $tableName = 'users';
}

$repo = new UserRepository($adapter);
$repo->setHydrator(new UserHydrator());

// PHPStan knows this is UserDto|null
$user = $repo->findObject(1);
```

---

## 2. Wiring with Adapters

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
│       ├── FilterParser.php # Phase 22 — Filter Parsing
│       ├── MySQLFilterBuilder.php # Phase 23 — SQL Builder
│       ├── MongoFilterBuilder.php # Phase 23 — Mongo Builder
│       ├── OrderUtils.php   # Phase 5 — Ordering & Sorting
│       ├── OrderParser.php  # Phase 24 — Order Parsing
│       ├── MySQLOrderBuilder.php # Phase 25 — SQL Order Builder
│       ├── MongoOrderBuilder.php # Phase 25 — Mongo Order Builder
│       ├── LimitOffsetValidator.php # Phase 6 — Limits & Offsets
│       ├── LimitOffsetConfig.php # Phase 27 — Limit Config
│       ├── ResultNormalizer.php # Phase 7 — Result Normalization
│       ├── NormalizerOptions.php # Phase 27 — Normalizer Options
│       ├── MysqlOps.php
│       ├── MongoOps.php
│       └── RedisOps.php
│
├── Hydration/        # Phase 11 — DTO Mapping & Object Hydration
│   ├── HydratorInterface.php
│   ├── HydrationContext.php
│   ├── BaseHydrator.php # Phase 12
│   ├── AutoCaster.php   # Phase 13
│   ├── MappingProfile.php # Phase 14
│   └── TransformerInterface.php
│
└── Pagination/       # Phase 15 — Pagination DTOs
    ├── PaginationEntry.php
    ├── PaginationContext.php
    ├── PaginationResultDTO.php
    └── HydratedPaginationCollection.php # Phase 17

```

---

## 📅 Roadmap & Phase Status

> The executor/roadmap engine drives this library in phases. The table below reflects the **current repository state**.

| Phase | Module                                    | Status      | Notes                              |
|-------|-------------------------------------------|-------------|------------------------------------|
| 1     | Bootstrap + Resolver + Exceptions         | ✅ Completed | Project foundation ready           |
| 2     | Base Repositories (MySQL / Redis / Mongo) | ✅ Completed | Drivers unified                    |
| 3     | CRUD Layer + Basic Filtering              | ✅ Completed | Implementing CRUD + filters        |
| 4     | Advanced Filtering (IN, LIKE, Ranges)     | ✅ Completed | SQL & Mongo                        |
| 5     | Ordering & Sorting                        | ✅ Completed | SQL, Mongo, Array Sorting          |
| 6     | Limits & Offsets                          | ✅ Completed | Unified Validation                 |
| 7     | Result Normalization                      | ✅ Completed | ID Mapping, Type Casting           |
| 8     | CRUD Edge Cases                           | ✅ Completed | Standardized Exceptions            |
| 9     | Generic Ops Integration                   | ✅ Completed | MysqlOps, MongoOps, RedisOps       |
| 10    | Pagination Preparation                    | ✅ Completed | DTOs only                          |
| 11    | Hydration Interfaces                      | ✅ Completed | Contract + Context                 |
| 12    | BaseHydrator + Pipeline                   | ✅ Completed | Base Implementation                |
| 13    | AutoCasting System                        | ✅ Completed | Strict Type Conversion             |
| 14    | DTO Mapping & Profiles                    | ✅ Completed | Transformers & Trait               |
| 15    | Pagination Core                           | ✅ Completed | Standardized paginate()            |
| 16    | Pagination Optimization                   | ✅ Completed | Efficient Driver Paging            |
| 17    | Paginated Hydrated Results                | ✅ Completed | Hydration + Pagination             |
| 18    | Integration Matrix                        | ✅ Completed | Fake vs Real Parity                |
| 19    | NoSQL Robustness                          | ✅ Completed | Mongo Collections, Redis Filtering |
| 20    | SQL & Filter Improvements                 | ✅ Completed | Semantic SQL, Safe BigInt          |
| 21    | Architecture Decoupling                   | ✅ Completed | Logger Injection                   |
| 22    | FilterParser Extraction                   | ✅ Completed | Decoupled Parsing Logic            |
| 23    | Filter Builders (MySQL + Mongo)           | ✅ Completed | Extracted Builder Logic            |
| 24    | OrderParser Extraction                    | ✅ Completed | Decoupled Order Logic              |
| 25    | Order Builders (MySQL + Mongo)            | ✅ Completed | Extracted Order Builders           |
| 26    | Public API Tightening                     | ✅ Completed | Audit & Strict API Surface         |
| 27    | NormalizerOptions + LimitOffsetConfig     | ✅ Completed | Runtime Configuration              |
| 28    | PHPStan Generics Templates                | ✅ Completed | Static Analysis & Strong Types     |
| 29    | DX & Documentation                        | ✅ Completed | Examples, Onboarding, Full Docs    |

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

- **Phase 5 — Ordering & Sorting**
  [`phases/README.phase5.md`](phases/README.phase5.md)

- **Phase 6 — Limits & Offsets**
  [`phases/README.phase6.md`](phases/README.phase6.md)

- **Phase 7 — Result Normalization**
  [`phases/README.phase7.md`](phases/README.phase7.md)

- **Phase 8 — CRUD Edge Cases**
  [`phases/README.phase8.md`](phases/README.phase8.md)

- **Phase 9 — Generic Ops Integration**
  [`phases/README.phase9.md`](phases/README.phase9.md)

- **Phase 10 — Pagination Preparation**
  [`phases/README.phase10.md`](phases/README.phase10.md)

- **Phase 11 — Hydrator Contracts**
  [`phases/README.phase11.md`](phases/README.phase11.md)

- **Phase 12 — Base Hydrator Implementation**
  [`phases/README.phase12.md`](phases/README.phase12.md)

- **Phase 13 — AutoCasting System**
  [`phases/README.phase13.md`](phases/README.phase13.md)

- **Phase 14 — DTO Mapping & Profiles**
  [`phases/README.phase14.md`](phases/README.phase14.md)

- **Phase 15 — Pagination Core**
  [`phases/README.phase15.md`](phases/README.phase15.md)

- **Phase 16 — Pagination Optimization**
  [`phases/README.phase16.md`](phases/README.phase16.md)

- **Phase 17 — Paginated Hydrated Results**
  [`phases/README.phase17.md`](phases/README.phase17.md)

- **Phase 18 — Integration Matrix**
  [`phases/README.phase18.md`](phases/README.phase18.md)

- **Phase 19 — NoSQL Robustness**
  [`phases/README.phase19.md`](phases/README.phase19.md)

- **Phase 20 — SQL & Filter Improvements**
  [`phases/README.phase20.md`](phases/README.phase20.md)

- **Phase 21 — Architecture Decoupling**
  [`phases/README.phase21.md`](phases/README.phase21.md)

- **Phase 22 — FilterParser Extraction**
  [`phases/README.phase22.md`](phases/README.phase22.md)

- **Phase 23 — Filter Builders**
  [`phases/README.phase23.md`](phases/README.phase23.md)

- **Phase 24 — OrderParser Extraction**
  [`phases/README.phase24.md`](phases/README.phase24.md)

- **Phase 25 — Order Builders (MySQL + Mongo)**
  [`phases/README.phase25.md`](phases/README.phase25.md)

- **Phase 26 — Public API Tightening**
  [`phases/README.phase26.md`](phases/README.phase26.md)

- **Phase 27 — NormalizerOptions + LimitOffsetConfig**
  [`phases/README.phase27.md`](phases/README.phase27.md)

- **Phase 28 — PHPStan Generics Templates**
  [`phases/README.phase28.md`](phases/README.phase28.md)

- **Phase 29 — DX & Documentation**
  [`phases/README.phase29.md`](phases/README.phase29.md)

---

## 📚 Modern Quick Start (Recommended)

The fastest way to start using `maatify/data-repository` is through
`DatabaseResolver` (from `maatify/data-adapters`) and any concrete repository you define.

### 1. Initialize the environment

```php
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;

$config   = new EnvironmentConfig(__DIR__);
$resolver = new DatabaseResolver($config);
````

---

### 2. Resolve an adapter (MySQL / Mongo / Redis)

```php
$mysql = $resolver->resolve('mysql.main', autoConnect: true);
$mongo = $resolver->resolve('mongo.logs', autoConnect: true);
$redis = $resolver->resolve('redis.cache', autoConnect: true);
```

Each adapter:

* Loads its connection config via DSN-first rules
* Uses the correct config builder (MySQL, Mongo, Redis)
* Connects only when needed (or immediately when autoConnect is true)

---

### 3. Create a Repository

```php
use App\Repository\UserRepository;

$userRepo = new UserRepository($mysql);
```

---

### 4. Perform basic operations

```php
$user = $userRepo->find(10);

$active = $userRepo->findBy(['active' => 1]);

$newId = $userRepo->insert([
    'name' => 'Ahmed',
    'email' => 'ahmed@example.com',
]);

$userRepo->update(['id' => $newId], ['active' => 0]);

$userRepo->delete(['id' => $newId]);
```

---

### 5. Mongo Example

```php
$logRepo = new LogRepository($mongo);

$logRepo->insert([
    'type' => 'error',
    'msg'  => 'Something happened'
]);

$errors = $logRepo->findBy(['type' => 'error']);
```

---

### 6. Redis Example

```php
$cacheRepo = new CacheRepository($redis);

$cacheRepo->insert([
    'key'   => 'theme',
    'value' => 'dark'
]);

$theme = $cacheRepo->findOneBy(['key' => 'theme']);
```

---

### ✔ That’s it.

You are now fully wired into MySQL, MongoDB, and Redis through the unified repository layer.

This is the correct and official quick-start flow of `maatify/data-repository`.


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
