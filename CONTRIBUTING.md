# Contributing to maatify/data-repository

[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

Thank you for considering contributing to **maatify/data-repository**!  
This project follows strict architectural and coding standards to ensure long-term stability across the entire Maatify ecosystem.

Please read the following guidelines before submitting issues, proposals, or pull requests.

---

# 📌 Ways to Contribute

You can contribute by:

- Reporting bugs
- Proposing new features
- Improving documentation
- Submitting pull requests (bug fixes, improvements, refactoring)
- Writing or improving test coverage

---

# 🧱 Project Structure

```

src/              Main library source
tests/            PHPUnit test suite
docs/dev/         Internal developer documentation
docs/user/        User-facing documentation

````

Refer to the master documentation index:  
👉 [`docs/dev/0-master/MASTER_DOCUMENTATION.md`](docs/dev/0-master/MASTER_DOCUMENTATION.md)

---

# 🧪 Running Tests

Before submitting any pull request, make sure all tests pass:

```bash
composer install
composer run test
````

To run static analysis:

```bash
composer run analyse
```

Minimum requirements:

* PHPStan level: **6** (no errors allowed)
* PHPUnit: all tests must pass
* Coverage: no regression

---

# 🧹 Code Style

This project follows:

* **PSR-12** coding standards
* **Strict Types** (`declare(strict_types=1)`)
* **Semantic & consistent naming**
* **No unused imports or dead code**
* **No mixed types unless documented**
* **Repository & Adapter interfaces MUST be respected**

Before pushing your changes:

```bash
composer run lint
composer run format
```

---

# 🧬 Commit Messages

Use clear, descriptive commit messages.
Recommended format:

```
type(scope): short description

Longer explanation (optional)
```

Examples:

* `fix(mysql-driver): correct filtering behavior`
* `feat(repository): add pagination support`
* `docs: update architecture diagrams`
* `refactor: cleanup hydration trait`

---

# 🌱 Branching Model

We use a simple branching workflow:

* `main` → stable releases
* `dev` → active development
* feature branches:
  `feature/<short-name>`
* bugfix branches:
  `fix/<short-name>`

---

# 🔀 Pull Request Guidelines

Before opening a PR:

1. Ensure code passes tests & analysis
2. Follow PSR-12 + project style rules
3. Update or add tests when needed
4. Update documentation if your change affects behavior
5. Keep PRs focused (small & clean)
6. Reference related issues
7. Add details in PR description explaining:

    * What changed
    * Why
    * How it was tested

PRs that fail tests or violate architectural rules may be rejected.

---

# 🧩 Architectural Rules

All contributors must follow the internal architecture rules located in:

👉 `docs/dev/`

Especially:

* `REPOSITORY_DESIGN.md`
* `REPOSITORY_FLOW.md`
* `DRIVERS_MATRIX.md`
* `DTO_RULES.md`
* `EXCEPTION_TAXONOMY.md`

This ensures that new additions do not break consistency across drivers.

---

# 🗂 Versioning

We follow **Semantic Versioning (SemVer)**:

```
MAJOR.MINOR.PATCH
```

* **PATCH** → bug fixes only
* **MINOR** → backward-compatible features
* **MAJOR** → breaking changes

Every release must be documented in:

👉 `CHANGELOG.md`

---

# 🔒 Security Vulnerabilities

To report a security issue, do **not** open a GitHub issue.
Instead, contact the Maatify security team at:

📧 **[security@maatify.dev](mailto:security@maatify.dev)**

See:

👉 [`SECURITY.md`](SECURITY.md)

---

# 🙏 Thank You!

Your contributions help make the Maatify ecosystem stronger and more reliable.
We appreciate your time, effort, and passion for clean architecture.

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a> — Unified Ecosystem for Modern PHP Libraries</sub>
</p>
