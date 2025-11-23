![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---

[![Version](https://img.shields.io/packagist/v/maatify/data-repository?label=Version&color=4C1)](https://packagist.org/packages/maatify/data-repository)
[![PHP](https://img.shields.io/packagist/php-v/maatify/data-repository?label=PHP&color=777BB3)](https://packagist.org/packages/maatify/data-repository)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-blue)](https://www.php.net/)

[![Build](https://github.com/Maatify/data-repository/actions/workflows/test.yml/badge.svg?label=Build&color=brightgreen)](https://github.com/Maatify/data-repository/actions/workflows/test.yml)

[![Monthly Downloads](https://img.shields.io/packagist/dm/maatify/data-repository?label=Monthly%20Downloads&color=00A8E8)](https://packagist.org/packages/maatify/data-repository)
[![Total Downloads](https://img.shields.io/packagist/dt/maatify/data-repository?label=Total%20Downloads&color=2AA9E0)](https://packagist.org/packages/maatify/data-repository)

[![Stars](https://img.shields.io/github/stars/Maatify/data-repository?label=Stars&color=FFD43B&cacheSeconds=3600)](https://github.com/Maatify/data-repository/stargazers)
[![License](https://img.shields.io/github/license/Maatify/data-repository?label=License&color=blueviolet)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Stable-success?style=flat-square)]()
[![Code Quality](https://img.shields.io/codefactor/grade/github/Maatify/data-repository/main?color=brightgreen)](https://www.codefactor.io/repository/github/Maatify/data-repository)

[![PHPStan](https://img.shields.io/badge/PHPStan-Level%206-4E8CAE)](https://phpstan.org/)
[![Coverage](https://img.shields.io/badge/Coverage-92%25-9C27B0)](#)

[![Changelog](https://img.shields.io/badge/Changelog-View-blue)](CHANGELOG.md)
[![Security](https://img.shields.io/badge/Security-Policy-important)](SECURITY.md)

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
- Driver normalization layer
- Repository Resolver
- DTO rules & hydration
- Filtering, pagination & runtime policies
- Exception taxonomy
- Testing and fake adapters compatibility

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
