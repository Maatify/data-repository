# Phase 21: Architecture Decoupling (Logger Injection)

[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](../../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

## Summary
Decoupled `BaseRepository` from `RepositoryLogger` to allow raw `Psr\Log\LoggerInterface` injection. This allows developers to inject any PSR-3 compliant logger without it being automatically wrapped in `RepositoryLogger`. The `RepositoryLogger` class remains available for manual usage if the "source" context is desired.

## Changes
- **Refactored**: `Maatify\DataRepository\Base\BaseRepository::__construct` now assigns the injected logger directly instead of wrapping it.
- **Added**: `tests/Architecture/LoggerInjectionTest.php` to verify logger injection behavior.

## Usage
### Before (Implicit Wrapping)
```php
$logger = new Monolog\Logger('app');
// Internally became new RepositoryLogger($logger)
$repo = new UserRepository($adapter, $logger);
```

### After (Raw Injection)
```php
$logger = new Monolog\Logger('app');
// Stays as $logger
$repo = new UserRepository($adapter, $logger);
```

To retain the previous behavior (adding `source` context):
```php
$logger = new Monolog\Logger('app');
$repo = new UserRepository($adapter, new RepositoryLogger($logger));
```
