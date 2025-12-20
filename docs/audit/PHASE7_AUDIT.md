# Phase 7 Audit — MongoDB Explicit Behavior

## Scope Reviewed
- `src/Generic/GenericMongoRepository.php`
- `src/Generic/Support/MongoOps.php`
- `src/Generic/Support/FilterUtils.php`
- `src/Generic/Support/MongoFilterBuilder.php`
- `tests/Generic/NoSQL/GenericMongoRepositoryTest.php` (Fake)
- `tests/Generic/Real/GenericMongoRepositoryRealTest.php`
- `tests/Generic/NoSQL/MongoRobustnessTest.php`
- `tests/Generic/Filters/MongoFilterBuilderTest.php`
- `docs/adr/ADR-005.md`
- `README.md`
- `docs/README.full.md`

## ADR-005 Compliance
- [x] find(id) casting rules
- [x] filter behavior
- [x] explicit-only policy
- [ ] documentation presence (README)
- [ ] documentation presence (Phase docs)

## Behavior Findings
- **Source Code**: The implementation in `GenericMongoRepository::find` correctly uses `buildIdFilter` to cast valid 24-char hex strings to `ObjectId`.
- **Filtering**: `MongoFilterBuilder` performs no automatic casting of values to `ObjectId`, adhering to the "explicit-only" policy for filters.
- **Normalization**: `MongoOps` handles `normalizeInsertedId` correctly but does not interfere with query parameter casting.
- **Parity**: Fake and Real adapters utilize the same `GenericMongoRepository` logic (via `RealAdapterTrait` and manual instantiation), ensuring behavioral parity.

## Deviations
- **Violation**: ADR-005 mandates "The casting behavior must be clearly documented: In README". The root `README.md` and `docs/README.full.md` contain no mention of MongoDB casting rules.
- **Violation**: Phase 7 documentation (`docs/phases/README.phase7.md`) is missing entirely from the filesystem, despite being referenced in the roadmap and strict process.
- **Violation**: ADR-005 mandates "Add regression tests for: Literal string IDs". While `GenericMongoRepositoryFakeTest` tests positive casting for `update`, there is no explicit test case ensuring that a hex string passed to `findBy` (as a filter value) remains a literal string (negative test case).

## Test Coverage Review
- **Exists**:
    - `find($id)` casting (implicitly covered in `GenericMongoRepositoryFakeTest`).
    - `update($id)` casting (explicitly covered in `GenericMongoRepositoryFakeTest::testHexStringIdIsConvertedToObjectId`).
    - Filter building structure (`MongoFilterBuilderTest`).
- **Missing**:
    - Explicit regression test proving `findBy(['some_field' => '507f1f77bcf86cd799439011'])` does **not** cast to `ObjectId`.
    - Tests for explicit `ObjectId` usage in filters (e.g., passing `new ObjectId(...)` to `findBy`).

## Documentation Review
- **Missing**: No documentation regarding MongoDB casting behavior found in `README.md` or `docs/README.full.md`.
- **Missing**: `docs/phases/README.phase7.md` does not exist.

## Audit Verdict
FAIL — Phase 7 violates ADR-005
