# Phase 28: PHPStan Generics Templates

[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](../../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

## Overview
Phase 28 introduces generic templates (`@template`) to core repository and hydration classes. This enables strict typing for entities returned by repositories and hydrators, significantly improving static analysis (PHPStan) capabilities for developers using the library.

## Changes

### 1. Hydration Generics
- `HydratorInterface` now defines `@template T of object`.
- `BaseHydrator` implements `HydratorInterface<T>`.
- `hydrate()` returns `T`.
- `hydrateAll()` returns `array<T>`.

### 2. Pagination Generics
- `HydratedPaginationCollection` defines `@template T of object`.
- Constructor and properties are typed as `array<T>`.

### 3. Repository Generics
- `BaseRepository` defines `@template T of object`.
- `GenericMySQLRepository`, `GenericMongoRepository`, and `GenericRedisRepository` extend the base with generic support.
- `RepositoryHydrationTrait` uses `@template T of object` and ensures methods like `findObject()` and `paginateObjects()` return strongly typed results (`T` or `HydratedPaginationCollection<T>`).

### 4. Static Analysis
- Added `phpstan.neon` to enforce strict template checks.
- Enabled `checkGenericClassInNonGenericObjectType`.

## Usage Example

```php
/**
 * @property int $id
 * @property string $name
 */
class UserEntity {}

/**
 * @extends GenericMySQLRepository<UserEntity>
 */
class UserRepository extends GenericMySQLRepository
{
    // ...
}

// When using the repository:
$user = $repo->findObject(1);
// PHPStan knows $user is UserEntity|null
```

## Impact
This phase ensures that downstream projects can rely on strict typing without needing excessive `@var` casting when working with hydrated objects.
