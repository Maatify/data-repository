# Project Snapshot — maatify/data-repository

**Role:** Core Repository Runtime Layer for Maatify Ecosystem  
**Type:** Library  
**Namespace:** `Maatify\DataRepository`  
**PHP Version:** `>=8.4`

---

## 1. Architectural Identity

This project provides the **Unified Data Access Layer** for the Maatify ecosystem.  
It abstracts differences between:

- **Storage Drivers**
    - MySQL (PDO / DBAL)
    - MongoDB
    - Redis (Predis / PhpRedis)

- **Execution Modes**
    - Real (Production)
    - Fake (Testing)

### Separation of Concerns

- **Adapters**
    - Provided by `maatify/data-adapters` and `maatify/data-fakes`
    - Handle connections and low-level access

- **Repositories**
    - Provided by `maatify/data-repository`
    - Handle:
        - Query normalization
        - Hydration
        - Pagination
        - Repository logic

### Dependency Rules

#### Must Depend On
- `maatify/common` (contracts, pagination DTOs)
- `maatify/data-adapters` (interfaces)
- `maatify/bootstrap`
- `maatify/psr-logger`

#### Must Not Depend On
- Application-specific business logic (Domain Layer)
- Framework HTTP layers (Slim, Laravel request/response objects)

---

## 2. Public API Surface

The following classes and interfaces constitute the **official public API**.  
All other components (Ops, Builders, internal helpers) are **internal**.

### Core Repositories
- `Maatify\DataRepository\Base\BaseRepository<T>`
- `Maatify\DataRepository\Generic\GenericMySQLRepository<T>`
- `Maatify\DataRepository\Generic\GenericMongoRepository<T>`
- `Maatify\DataRepository\Generic\GenericRedisRepository<T>`
- `Maatify\DataRepository\Resolver\RepositoryResolver`

### Hydration & DTOs
- `Maatify\DataRepository\Hydration\HydratorInterface<T>`
- `Maatify\DataRepository\Hydration\BaseHydrator`
- `Maatify\DataRepository\Hydration\AutoCaster`
- `Maatify\DataRepository\Hydration\MappingProfile`
- `Maatify\DataRepository\Hydration\TransformerInterface`
- `Maatify\DataRepository\Generic\Support\RepositoryHydrationTrait`

### Pagination & Results
- `Maatify\DataRepository\Pagination\PaginationResultDTO`  
  *(Alias to Common DTO)*
- `Maatify\DataRepository\Pagination\HydratedPaginationCollection<T>`
- `Maatify\DataRepository\Pagination\PaginationEntry`
- `Maatify\DataRepository\Pagination\PaginationContext`

### Utilities & Configuration
- `Maatify\DataRepository\Generic\Support\FilterUtils`
- `Maatify\DataRepository\Generic\Support\OrderUtils`
- `Maatify\DataRepository\Generic\Support\LimitOffsetValidator`
- `Maatify\DataRepository\Generic\Support\ResultNormalizer`
- `Maatify\DataRepository\Generic\Support\NormalizerOptions`
- `Maatify\DataRepository\Generic\Pagination\LimitOffsetConfig`

### Value Objects (Support)
- `Maatify\DataRepository\Generic\Support\FieldFilter`
- `Maatify\DataRepository\Generic\Support\FilterParser`
- `Maatify\DataRepository\Generic\Support\OrderField`
- `Maatify\DataRepository\Generic\Support\OrderParser`

---

## 3. Phase Status

Verified against `roadmap.json` and `src/` content.

| Phase | Title                           | Status    | Evidence                                    |
|------:|---------------------------------|-----------|---------------------------------------------|
|    01 | Bootstrap, Exceptions, Resolver | ✅ DONE    | `RepositoryResolver`, `RepositoryException` |
|    02 | Base Repository Layer           | ✅ DONE    | `BaseRepository`, base drivers              |
|    03 | CRUD Layer                      | ✅ DONE    | Generic repositories                        |
|    04 | Advanced Filtering              | ✅ DONE    | `FilterUtils`, `FilterParser`               |
|    05 | Ordering & Sorting              | ✅ DONE    | `OrderUtils`, `OrderParser`                 |
|    06 | Limits & Offsets                | ✅ DONE    | `LimitOffsetValidator`                      |
|    07 | Result Normalization            | ✅ DONE    | `ResultNormalizer`                          |
|    08 | CRUD Edge Cases                 | ✅ DONE    | Test coverage (phase-output)                |
|    09 | Generic Ops Integration         | ✅ DONE    | MySQL / Mongo / Redis Ops                   |
|    10 | Pagination Hooks                | ✅ DONE    | `PaginationEntry`                           |
|    11 | Hydrator Contract               | ✅ DONE    | `HydratorInterface`                         |
|    12 | Base Hydrator Pipeline          | ✅ DONE    | `BaseHydrator`                              |
|    13 | Auto Casting System             | ✅ DONE    | `AutoCaster`                                |
|    14 | DTO Mapping & Profiles          | ✅ DONE    | `MappingProfile`, trait                     |
|    15 | Pagination Core                 | ✅ DONE    | `paginate()`                                |
|    16 | Pagination Optimization         | ✅ DONE    | Optimized limit/offset                      |
|    17 | Paginated Hydrated Results      | ✅ DONE    | `HydratedPaginationCollection`              |
|    18 | Integration Matrix              | ✅ DONE    | `IntegrationValidatorTest`                  |
|    19 | NoSQL Robustness                | ✅ DONE    | Redis in-memory filtering                   |
|    20 | SQL & Filter Improvements       | ✅ DONE    | Semantic placeholders                       |
|    21 | Architecture Decoupling         | ✅ DONE    | Logger injection refactor                   |
|    22 | FilterParser Extraction         | ✅ DONE    | `FilterParser`, `FieldFilter`               |
|    23 | Filter Builders                 | ✅ DONE    | MySQL / Mongo builders                      |
|    24 | OrderParser Extraction          | ✅ DONE    | `OrderParser`, `OrderField`                 |
|    25 | Order Builders                  | ✅ DONE    | MySQL / Mongo builders                      |
|    26 | Public API Tightening           | ✅ DONE    | Visibility audit                            |
|    27 | Normalizer / Limit Config       | ✅ DONE    | Config objects                              |
|    28 | PHPStan Generics                | ✅ DONE    | `@template T` usage                         |
|    29 | DX & Documentation              | ✅ DONE    | `README.full.md`, examples                  |
|    30 | Final Release v1.0.0            | ✅ DONE    | Version finalized                           |
|   31+ | Future Stability / Features     | ⏳ PLANNED | See roadmap                                 |

---

## 4. Known Gaps & Constraints

- **Test Execution**
    - Environment lacks PHP binaries in `$PATH`
    - Validation performed via static analysis and code review

- **Redis Filtering**
    - Filtering is done in-memory after key fetch
    - Not suitable for massive datasets without secondary indexing

- **Strict Mode**
    - File system modifications restricted to designated outputs

---

## 5. Strict Rules for Future LLMs

- **Single Source of Truth**
    - This file (`llm-snapshot.md`) overrides conversational memory

- **No Speculation**
    - Do not implement *planned* phases unless explicitly instructed

- **Verify First**
    - Always check:
        - `phase-output.json`
        - `roadmap.json`
        - `src/`

- **Preserve Architecture**
    - Do not bypass `BaseRepository` or `HydratorInterface`
    - Maintain separation between data access and hydration

---

## Validation Report

### Files Used
- `roadmap.json`
- `phase-output.json`
- `api-map.json`
- `composer.json`
- `src/Generic/GenericMySQLRepository.php`
- `src/Generic/GenericMongoRepository.php`
- `src/Generic/GenericRedisRepository.php`

### Files Missing
- `WORK_SYSTEM.md`  
  *(Not required — roadmap artifacts provided sufficient context)*

### Confidence Level
**HIGH**

All completed phases (1–30) have corresponding:
- Source code
- Output records
- Public API definitions

The project state is **consistent with a stable v1.0.0 release**.
