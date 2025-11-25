# Phase 2: Base Repository Layer
[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](../../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)


## Status: COMPLETED
**Date:** 2025-11-25
**Version:** 1.0.0 (Dev)

## Summary
This phase established the abstract `BaseRepository` layer, which serves as the foundation for all specific repository implementations. It successfully normalized driver access across multiple adapters (PDO vs DBAL, Redis vs Predis, Mongo Client vs Database) and enforced strict type validation to ensure the correct adapter is used for each repository type.

## Deliverables
### Core Abstracts
- `Maatify\DataRepository\Base\BaseRepository`: Parent class implementing `RepositoryInterface` with logger and adapter injection.
- `Maatify\DataRepository\Base\BaseMySQLRepository`: Validates and accepts `PDO` or `Doctrine\DBAL\Connection`.
- `Maatify\DataRepository\Base\BaseMongoRepository`: Validates and accepts `MongoDB\Client` or `MongoDB\Database`.
- `Maatify\DataRepository\Base\BaseRedisRepository`: Validates and accepts `Redis` or `Predis\Client`.

### Testing
- **Fake Integration**: Validated that `FakeAdapters` (from `maatify/data-fakes`) work seamlessly with the base repositories.
- **Real Integration**: Validated that `RealAdapters` (from `maatify/data-adapters`) are correctly recognized and their drivers (PDO, etc.) are accessible.

## Technical Notes
- **Driver Normalization**:
    - Mongo: Handles both `Client` (requires db name) and `Database` (pre-selected db) logic in `getCollection()`.
    - MySQL/Redis: Abstracted validation logic allows switching between drivers (e.g., `phpredis` to `predis`) without changing repository code.
- **Strict Typing**: All code complies with PHPStan Level Max, using conditional return types in tests to satisfy interface contracts while avoiding "unused type" errors.

## Dependencies
- `maatify/data-adapters`: Required for real integration tests.
- `maatify/data-fakes`: Required for fake integration tests.