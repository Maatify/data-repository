![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---

## 🚀 Maatify Data Repository — Badges

<!-- 1) Package Info -->
[![Version](https://img.shields.io/packagist/v/maatify/data-repository?label=Version&color=4C1&style=flat-square)](https://packagist.org/packages/maatify/data-repository)
[![PHP](https://img.shields.io/packagist/php-v/maatify/data-repository?label=PHP&color=777BB3&style=flat-square)](https://packagist.org/packages/maatify/data-repository)
[![License](https://img.shields.io/github/license/Maatify/data-repository?label=License&color=blueviolet&style=flat-square)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Stable-success?style=flat-square)](CHANGELOG.md)

<!-- 2) CI / QA -->
[![Tests](https://github.com/Maatify/data-repository/actions/workflows/test.yml/badge.svg?style=flat-square)](https://github.com/Maatify/data-repository/actions/workflows/test.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%206-4E8CAE?style=flat-square)](https://phpstan.org/)
[![Code Quality](https://img.shields.io/codefactor/grade/github/Maatify/data-repository/main?color=brightgreen&style=flat-square)](https://www.codefactor.io/repository/github/Maatify/data-repository)
[![Coverage](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/Maatify/data-repository/badges/coverage.json&style=flat-square)]()


<!-- 3) Popularity -->
[![Monthly Downloads](https://img.shields.io/packagist/dm/maatify/data-repository?label=Monthly%20Downloads&color=00A8E8&style=flat-square)](https://packagist.org/packages/maatify/data-repository)
[![Total Downloads](https://img.shields.io/packagist/dt/maatify/data-repository?label=Total%20Downloads&color=2AA9E0&style=flat-square)](https://packagist.org/packages/maatify/data-repository)
[![Stars](https://img.shields.io/github/stars/Maatify/data-repository?label=Stars&color=FFD43B&cacheSeconds=3600&style=flat-square)](https://github.com/Maatify/data-repository/stargazers)

<!-- 4) Documentation -->
<!-- 4) Documentation -->
[![Changelog](https://img.shields.io/badge/Changelog-View-blue?style=flat-square)](CHANGELOG.md)
[![Security](https://img.shields.io/badge/Security-Policy-important?style=flat-square)](SECURITY.md)
[![Full Docs](https://img.shields.io/badge/Docs-Full%20Guide-0A66C2?style=flat-square)](docs/README.full.md)
[![Contributing](https://img.shields.io/badge/Contributing-Guide-0A9396?style=flat-square)](CONTRIBUTING.md)
[![Code of Conduct](https://img.shields.io/badge/Code%20of%20Conduct-Community-EE9B00?style=flat-square)](CODE_OF_CONDUCT.md)
---

# Maatify Data Repository

A high-performance, multi-driver repository layer designed for the entire Maatify ecosystem.  
This package provides a unified, abstracted, and fully testable data-access layer that works consistently across MySQL, MongoDB, Redis, and DBAL adapters.

---

## 📌 Overview

`maatify/data-repository` defines the core architecture for building consistent repository classes,  
normalizing driver behavior, and ensuring predictable data flows across all storage systems.

It is designed to:

- Unify adapter behavior (MySQL, Redis, MongoDB, DBAL)
- Provide a consistent repository abstraction
- Enforce strict typing and predictable data structures
- Support hydration, filtering, pagination, and validation rules
- Enable fakes and testing layers across all drivers

---

## 📁 Documentation

Developer documentation is located under:

**`docs/dev/`**

Full index:

👉 [`docs/dev/0-master/MASTER_DOCUMENTATION.md`](docs/dev/0-master/MASTER_DOCUMENTATION.md)

User-facing docs (installation, usage examples, API reference) will be added under:

**`docs/user/`**

---

## 🧩 Core Concepts

This package is built around:

- RepositoryInterface — unified CRUD contracts
- Base repositories that normalize adapters for MySQL (PDO/DBAL), MongoDB, and Redis/Predis
- Generic repositories for MySQL, MongoDB, and Redis that provide CRUD utilities over validated drivers
- Driver normalization layer
- Repository Resolver
- DTO rules & hydration
- Filtering, pagination & runtime policies
- Exception taxonomy
- Testing and fake adapters compatibility
- Base repositories for MySQL (PDO/DBAL), MongoDB (Database/Collection), and Redis (phpredis/Predis) with unified driver accessors

(All documented inside `docs/dev/`.)

---

## 🚀 Roadmap

This README will expand automatically as project phases progress.

Upcoming additions include:

- Installation & configuration guide
- Usage examples
- Repository scaffolding guide
- Driver-specific behaviors
- Testing instructions
- Advanced features (caching, decorators, observers)

---

## 🛠 Requirements

- PHP 8.4+
- Composer
- maatify/data-adapters
- maatify/common

---

## 📝 Full Documentation

👉 **[`README.full.md`](docs/README.full.md)**

---

## 🤝 Contributing

Contribution guidelines and development workflow:

**`docs/dev/8-handbook/HANDBOOK.md`**

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

Special thanks to the Maatify.dev engineering team and open-source contributors.

---

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a> — Unified Ecosystem for Modern PHP Libraries</sub>
</p>
