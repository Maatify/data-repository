# Maatify Data Repository

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-8892BF.svg)](https://php.net/)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%20Max-4E8CAE)](https://phpstan.org/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

**Unified Repository Abstraction Layer** for Maatify ecosystems.
Normalize interactions across **MySQL (PDO/DBAL)**, **MongoDB**, and **Redis** with a single, consistent API.

---

## 🚀 Features

- **Unified CRUD**: One interface (`find`, `insert`, `update`, `delete`) for SQL and NoSQL.
- **Advanced Filtering**: Support for `IN`, `LIKE`, `BETWEEN`, `>`, `<`, and more across all drivers.
- **Result Normalization**: Consistent array structures regardless of the underlying driver.
- **Strict Validation**: Enforced pagination limits, offsets, and input types.
- **Hydration & DTOs**: Built-in support for mapping raw results to typed objects via Generics (`@template T`).
- **PHPStan Generic Support**: Fully typed for static analysis at level `max`.

---

## 📦 Installation

```bash
composer require maatify/data-repository
```

## 🛠 Basic Usage

See `examples/` for detailed usage scripts.

```php
use Maatify\DataRepository\Generic\GenericMySQLRepository;

// Define your entity
class User {}

/**
 * @extends GenericMySQLRepository<User>
 */
class UserRepository extends GenericMySQLRepository
{
    protected string $tableName = 'users';
}

$repo = new UserRepository($adapter);
$user = $repo->find(1); // Returns array|null
$object = $repo->findObject(1); // Returns User|null
```

## 🧪 Testing

Run unit tests:
```bash
composer run-script test
```

Run static analysis:
```bash
composer run-script analyse
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
