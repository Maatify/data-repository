# Maatify Data Repository — Practical Usage Guide

This guide provides a complete and practical overview of how to use the
`maatify/data-repository` library.  
It is intentionally written in a clear, simple, and developer-friendly manner.  
No internals, no unnecessary theory — just everything you need to **use** the library effectively.

---

# Table of Contents

1. Introduction  
2. Installation  
3. Core Concepts  
4. Adapter Initialization  
5. Creating Repositories  
6. MySQL Repository Usage  
7. MongoDB Repository Usage  
8. Redis Repository Usage  
9. Filters (Full Usage Guide)  
10. Sorting  
11. Limits & Offsets  
12. Pagination  
13. DTOs & Hydration  
14. Error Handling  
15. Raw Driver Access  
16. Using Fake Adapters for Testing  
17. Best Practices  
18. Common Mistakes  
19. Final Notes  

---

# 1. Introduction

`maatify/data-repository` provides a unified, adapter-agnostic repository layer
that works consistently across:

- MySQL (PDO / DBAL)
- MongoDB
- Redis
- Fake in-memory drivers (for tests)

You write one repository class, and it works with real and fake adapters
without modification.

---

# 2. Installation

```bash
composer require maatify/data-repository
````

### Required for real database usage:

```bash
composer require maatify/data-adapters
```

> `data-repository` is adapter-agnostic and does not require specific drivers at installation time.
> For real MySQL, MongoDB, or Redis usage, install `maatify/data-adapters`.

### Optional (for testing):

```bash
composer require maatify/data-fakes
```

---

# 3. Core Concepts

### Repository

Your data-access layer.
Provides CRUD, filtering, sorting, pagination, hydration, etc.

### Adapter

A wrapper around an actual database driver.

### Fake Adapter

In-memory adapters for deterministic testing.

### Filters

An expressive array syntax automatically converted into SQL/Mongo conditions.

### Hydrator / DTO

Converts raw arrays into typed PHP objects.

---

# 4. Adapter Initialization

Maatify Data Repository works only with adapters provided by
`maatify/data-adapters`.

All adapters share:

- A unified configuration system (`EnvironmentConfig`)
- Connection builders (`MySqlConfigBuilder`, `MongoConfigBuilder`, `RedisConfigBuilder`)
- A DSN-first strategy
- Multi-profile support (`main`, `logs`, `analytics`, …)

Because configuration resolution happens **inside BaseAdapter**,  
adapters **cannot be manually instantiated with raw drivers**.  
They must be created through **DatabaseResolver**.

---

## 4.1. Using DatabaseResolver (Required)

`DatabaseResolver` loads configuration from `.env` using EnvironmentConfig.
It then instantiates the correct adapter and injects the proper profile.

This is the **only supported and recommended** method.

### Example `.env`

```

MYSQL_MAIN_DSN=mysql:host=127.0.0.1;dbname=test;charset=utf8mb4
MYSQL_MAIN_USER=root
MYSQL_MAIN_PASS=secret

MYSQL_DBAL_MAIN_URL=mysql://root:secret@127.0.0.1/test

MONGO_MAIN_DSN=mongodb://127.0.0.1:27017
MONGO_MAIN_DATABASE=testdb

REDIS_MAIN_HOST=127.0.0.1
REDIS_MAIN_PORT=6379

PREDIS_MAIN_URI=redis://127.0.0.1:6379

````

### Resolver Bootstrapping

```php
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;

$config   = new EnvironmentConfig(__DIR__);
$resolver = new DatabaseResolver($config);

// Each adapter is resolved by a profile name
$mysql     = $resolver->resolve('mysql.main');
$mysqlDbal = $resolver->resolve('mysql_dbal.main');
$mongo     = $resolver->resolve('mongo.main');
$redis     = $resolver->resolve('redis.main');
$predis    = $resolver->resolve('predis.main');
````

### Adapter Usage

```php
$users = new UserRepository($mysql);
$logs  = new LogRepository($mongo);

if (! $mongo->healthCheck()) {
    throw new RuntimeException('MongoDB is not reachable');
}
```

---

## 4.2. Manual Instantiation (Not Supported)

Adapters **cannot** be directly instantiated like:

```php
new MySQLAdapter($pdo);        // ❌ invalid
new MongoAdapter($client);     // ❌ invalid
new RedisAdapter($redis);      // ❌ invalid
```

### Why?

Because each adapter requires:

* Profile name injection
* EnvironmentConfig instance
* Builder resolution (DSN → registry → legacy fallback)
* Automatic authentication options
* Connection validation
* Internal config mapping via ConnectionConfigDTO

All of this is handled **inside BaseAdapter**, *not* in user land.

### Therefore:

> **You must always use DatabaseResolver to construct adapters.**

---

## 4.3. Choosing the Right Adapter

| Adapter              | Backend        | Notes                                         |
|----------------------|----------------|-----------------------------------------------|
| **MySQLAdapter**     | PDO            | Lightweight, simple SQL workloads             |
| **MySQLDbalAdapter** | Doctrine DBAL  | Advanced SQL, portability                     |
| **MongoAdapter**     | MongoDB\Client | DSN-first, full builder support               |
| **RedisAdapter**     | phpredis       | Best performance                              |
| **PredisAdapter**    | Predis\Client  | Pure PHP, fallback when extension unavailable |

---

## 4.4. Summary

* ✔ Adapters **must** be created through DatabaseResolver
* ✔ Manual instantiation is **not allowed**
* ✔ All configuration flows through EnvironmentConfig + Builders
* ✔ Repositories accept any adapter implementing AdapterInterface
* ✔ Internal logic (DSN mode, fallback mode, health check, reconnect) is automatically handled

---

# 5. Creating Repositories

Your custom repository extends one of:

* `BaseMySQLRepository`
* `BaseMongoRepository`
* `BaseRedisRepository`

### MySQL Example

```php
class UserRepository extends BaseMySQLRepository
{
    protected string $tableName = 'users';
}
```

### Mongo Example

```php
class LogRepository extends BaseMongoRepository
{
    protected string $collectionName = 'logs';
}
```

### Redis Example

```php
class CacheRepository extends BaseRedisRepository
{
    protected string $keyPrefix = 'cache:';
}
```

---

# 6. MySQL Repository Usage

```php
$userRepo = new UserRepository($mysqlAdapter);
```

### Find by ID

```php
$user = $userRepo->find(10);
```

### Find with filters

```php
$rows = $userRepo->findBy(['active' => 1]);
```

### Insert

```php
$id = $userRepo->insert([
    'name' => 'Ahmed',
    'email' => 'ahmed@example.com',
]);
```

### Update

```php
$userRepo->update(['id' => $id], ['active' => 0]);
```

### Delete

```php
$userRepo->delete(['id' => $id]);
```

### Count

```php
$total = $userRepo->count(['active' => 1]);
```

---

# 7. MongoDB Repository Usage

```php
$logRepo = new LogRepository($mongoAdapter);
```

### Insert

```php
$logRepo->insert([
    'type' => 'error',
    'message' => 'Something happened',
]);
```

### Find

```php
$logs = $logRepo->findBy(['type' => 'error']);
```

### Count

```php
$count = $logRepo->count(['type' => 'error']);
```

---

# 8. Redis Repository Usage

```php
$cacheRepo = new CacheRepository($redisAdapter);
```

### Insert

```php
$cacheRepo->insert([
    'key' => 'user:1',
    'value' => json_encode(['name' => 'Ahmed']),
]);
```

### Fetch

```php
$value = $cacheRepo->findOneBy(['key' => 'user:1']);
```

### Delete

```php
$cacheRepo->delete(['key' => 'user:1']);
```

---

# 9. Filters — Full Usage Guide

### Equality

```php
['active' => 1]
```

### IN

```php
['id IN' => [1, 2, 3]]
```

### NOT IN

```php
['status NOT IN' => ['deleted', 'archived']]
```

### LIKE

```php
['email LIKE' => '%gmail.com']
```

### Comparison operators

```php
['age >' => 18, 'age <=' => 55]
```

### NULL checks

```php
['deleted_at IS' => null]
['verified_at IS NOT' => null]
```

---

# 10. Sorting

```php
$orderBy = [
    'created_at' => 'DESC',
    'id' => 'ASC',
];

$rows = $repo->findBy(['active' => 1], $orderBy);
```

---

# 11. Limits & Offsets

```php
$rows = $repo->findBy(
    ['active' => 1],
    ['id' => 'DESC'],
    limit: 50,
    offset: 100
);
```

---

# 12. Pagination

```php
$page = $userRepo->paginate(
    filters: ['active' => 1],
    orderBy: ['created_at' => 'DESC'],
    page: 2,
    perPage: 20
);
```

Returns:

* `items`
* `totalItems`
* `totalPages`
* `page`
* `perPage`

---

# 13. DTOs & Hydration

### DTO

```php
class UserDTO {
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {}
}
```

### Hydrator

```php
class UserHydrator implements HydratorInterface {
    public function hydrate(array $row): UserDTO {
        return new UserDTO($row['id'], $row['name'], $row['email']);
    }
}
```

---

# 14. Error Handling

```php
try {
    $repo->findBy([], [], limit: -1);
} catch (RepositoryException $e) {
    // handle error
}
```

---

# 15. Raw Driver Access

```php
$pdo   = $userRepo->getPdo();
$mongo = $logRepo->getMongo();
$redis = $cacheRepo->getRedis();
```

---

# 16. Using Fake Adapters for Testing

```php
$storage = new FakeStorageLayer();
$fake = new FakeMySQLAdapter($storage);

$storage->seed('users', [
    ['id' => 1, 'name' => 'Test', 'active' => 1],
]);

$repo = new UserRepository($fake);

$result = $repo->findBy(['active' => 1]);
```

---

# 17. Best Practices

* One repository per table/collection
* Keep filters simple
* Avoid business logic inside repositories
* DTOs for domain-rich structures
* FakeAdapters for fast and isolated tests
* Validate input in services before passing it to the repository

---

# 18. Common Mistakes

❌ Invalid limits/offsets  
❌ Putting business logic inside repositories  
❌ Over-using Redis for queries  
❌ Forgetting to seed FakeAdapters in tests  
❌ Overcomplicating filters  

---

# 19. Final Notes

`maatify/data-repository` provides a clean, unified, framework-agnostic API for
interacting with MySQL, MongoDB, Redis, and fake adapters with consistent behavior.

Use this guide as your main reference for building clean, reusable, and testable
data-access layers.
