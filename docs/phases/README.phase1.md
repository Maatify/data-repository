# Phase 1: Project Bootstrap & Core Architecture

[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](../../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

## Status: COMPLETED
**Date:** 2025-11-25
**Version:** 1.0.0

## Summary
Established the foundation of `maatify/data-repository`. This phase focused on setting up the directory structure, dependency management, and the core `RepositoryResolver` pattern. The architecture strictly enforces PSR-12 coding standards, strong typing, and dependency injection for logging and adapters.

## Deliverables
1. **Core Resolver**: `RepositoryResolver` capable of registering and retrieving generic `AdapterInterface` implementations.
2. **Exception Handling**: `RepositoryException` for standardized error reporting (driver support, connections).
3. **Logging**: `RepositoryLogger` decorator ensuring all repository logs carry the correct `source` context.
4. **Testing Infrastructure**:
    - `FakeSmokeTest`: Verifies resolver logic using in-memory mocks.
    - `RealSmokeTest`: Verifies architectural integrity using PHPUnit attributes.

## Technical Notes
- **PHP Version**: 8.1+ (Tested on 8.4).
- **Static Analysis**: Level Max (Strict types for iterables enforced).
- **Dependencies**:
    - `maatify/common`: Contracts.
    - `maatify/data-adapters`: Real driver support (contracts).
    - `maatify/psr-logger`: Logging ecosystem compatibility.

## Usage Example
```php
use Maatify\DataRepository\Resolver\RepositoryResolver;
use Maatify\DataFakes\Adapters\MySQL\FakeMySQLAdapter;
use Maatify\DataFakes\Storage\FakeStorageLayer;

// Setup
$storage = new FakeStorageLayer();
$adapter = new FakeMySQLAdapter($storage);
$resolver = new RepositoryResolver();

// Registration
$resolver->registerAdapter('main_db', $adapter);

// Retrieval
$repo = $resolver->getAdapter('main_db');
```