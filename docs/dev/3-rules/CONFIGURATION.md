![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
> 🔙 **[Back to Master Documentation](../0-master/MASTER_DOCUMENTATION.md)**
---
# ⚙️ Configuration Rules  
**Project:** maatify/data-repository  
**Version:** 1.0.0  
**Status:** Authoritative  
**Purpose:** Define the configuration sources, bootstrapping rules, environment dependencies, and allowable configuration patterns for all repositories.

Repository configuration MUST be deterministic, centralized, and compatible with:

- maatify/bootstrap (EnvHelper, PathHelper, IntegrationValidator)
- maatify/common (contracts, DTOs, Validators)
- maatify/data-adapters (real drivers)
- maatify/data-fakes (fake drivers for testing)

---

# 📚 Table of Contents

1. [Configuration Philosophy](#configuration-philosophy)  
2. [Configuration Sources](#configuration-sources)  
3. [Mandatory Bootstrap Dependencies](#mandatory-bootstrap-dependencies)  
4. [Adapter Configuration](#adapter-configuration)  
5. [Repository Configuration](#repository-configuration)  
6. [Environment Variables](#environment-variables)  
7. [Validation of Configuration](#validation-of-configuration)  
8. [Forbidden Configuration Patterns](#forbidden-configuration-patterns)  
9. [Runtime Configuration](#runtime-configuration)  
10. [Testing Configuration](#testing-configuration)  
11. [Default Values](#default-values)  
12. [Summary](#summary)

---

# 🧠 Configuration Philosophy

Repository configuration MUST be:

- **Explicit** (never inferred dynamically)  
- **Deterministic** (no auto-magic guessing)  
- **Minimal** (repositories shouldn't need many config values)  
- **Centralized** (bootstrap loads config, repository consumes it)  
- **Environment-driven** (real adapters receive DSNs from env)  
- **Immutable at runtime** (no config mutation allowed)  

Repositories MUST NOT read environment variables directly.  
Repositories MUST NOT parse `.env` files directly.  
Repositories MUST NOT load configuration files themselves.

ONLY bootstrap layer is allowed to load configuration.

---

# 🟦 Configuration Sources

Repository Layer MAY ONLY receive configuration from:

### ✔ 1. `maatify/bootstrap`
- EnvHelper  
- PathHelper  
- IntegrationValidator  

### ✔ 2. Dependency Injection
- AdapterInterface  
- LoggerInterface  
- HydratorInterface (if custom)  

### ✔ 3. Internal definitions
- repository schema definition  
- allowed filter fields  
- allowed sort fields  
- allowed update fields  

Repository MUST NOT depend on external global configuration.

---

# 🔌 Mandatory Bootstrap Dependencies

The following MUST exist:

### ✔ EnvHelper  
Provides access to environment settings via:

```php
EnvHelper::get('DB_DSN');
EnvHelper::getInt('REDIS_PORT');
EnvHelper::getBool('MONGO_SSL');
````

### ✔ PathHelper

Used for locating configuration files, migrations, etc.

### ✔ IntegrationValidator

Confirms correct wiring:

* adapter implements AdapterInterface
* logger is PSR-3 compliant
* environment loaded correctly
* fake drivers not used in production

---

# 🟩 Adapter Configuration

Every AdapterInterface passed to repository MUST:

### ✔ already be configured

### ✔ already be connected (if autoConnect=true)

### ✔ already have validated DSN

### ✔ already know driver type (PDO, DBAL, Mongo, Redis…)

Repository MUST NOT:

❌ open driver connections manually
❌ parse DSNs
❌ modify adapter configuration
❌ switch databases at runtime

Adapter configuration belongs 100% to **data-adapters**.

---

# 🧱 Repository Configuration

Repository MAY define:

* allowed fields
* required fields
* allowed filter operators
* allowed sort fields
* pagination max limit
* DTO class mappings

Repository MAY NOT:

❌ read .env
❌ read JSON/YAML config
❌ override adapter config
❌ apply dynamic configuration from user input

---

# 🌍 Environment Variables

Repositories MUST NOT use:

```
getenv()
$_ENV
$_SERVER
parse_ini_file
```

ALL environment loading MUST come from:

```
maatify/bootstrap: EnvHelper
```

Adapter configuration is handled in:

```
maatify/data-adapters
```

Fake configuration is handled in:

```
maatify/data-fakes
```

---

# 🧪 Validation of Configuration

`IntegrationValidator` MUST:

* validate adapter type
* validate logger exists
* validate repository class constraints
* reject unsupported driver type
* prevent FakeAdapter usage in production
* prevent RealAdapter usage inside pure-fake unit tests (unless real test mode enabled)

Repositories MUST NOT bypass validator.

---

# 🚫 Forbidden Configuration Patterns

Repository MUST NOT:

❌ load YAML or JSON config files
❌ depend on static config arrays
❌ declare global configuration variables
❌ mutate configuration during runtime
❌ interpret values from user input as configuration
❌ change adapter settings (host, port, db name…)
❌ depend on stateful config objects

Forbidden example:

```php
$this->dbName = $_GET['db'] ?? 'default';
```

---

# ⏱ Runtime Configuration

Runtime repository behavior (filtering, pagination, hydration)
MUST NOT depend on environment variables.

Only adapter behavior depends on environment (e.g., DB DSN).

Repository behavior MUST be:

* static
* defined
* deterministic
* version-controlled

---

# 🧪 Testing Configuration

Testing MUST set:

```
APP_ENV=testing
```

Fake drivers MUST be enabled under:

* phpunit.xml
* CI pipeline
* FakeEnvironment bootstrapping

Repository tests MUST NOT override configuration manually.

FakeEnvironment MUST automatically reset:

* FakeStorageLayer
* FakeRedis
* FakeMongo
* Simulation layers

---

# ⭐ Default Values

Repositories SHOULD define defaults for:

* pagination limit
* sort direction
* optional fields in insert/update
* empty filters
* empty payload fields

Defaults MUST be safe.

---

# 🧩 Summary

Repository configuration MUST:

* rely on bootstrap only
* receive configuration from adapters, not build it
* follow deterministic rules
* never depend on global state
* never allow user-controlled config
* stay immutable during runtime
* use EnvHelper for environment
* use IntegrationValidator for safety

This document MUST NOT change
except through a roadmap-approved update.
