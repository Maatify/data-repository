# Phase 7: Result Normalization Layer

[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](../../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

## Summary
The Result Normalization Layer ensures that data retrieved from different repository drivers (MySQL, MongoDB, Redis) conforms to a unified structure before being passed to the application or hydration layer. This phase introduces `ResultNormalizer` to handle key mapping and type casting with a robust, configurable API.

## Changes

### Added
- `src/Generic/Support/ResultNormalizer.php`: Central class for normalizing single rows and result sets with recursive and configuration capabilities.
- `tests/Generic/Normalization/ResultNormalizerTest.php`: Unit tests verifying normalization logic.

### Key Features
- **ID Normalization**:
  - Automatically maps MongoDB `_id` to `id` if `id` is missing.
  - Removes redundant `_id` by default (configurable via `keepMongoId`).
- **Type Casting**:
  - Converts `MongoDB\BSON\ObjectId` to string.
  - Converts stringable objects (implementing `__toString`) to strings.
  - Preserves integers and numeric strings appropriately.
- **Recursive Normalization**: Can optionally traverse nested arrays to normalize `ObjectId`s deep within the structure.
- **Strict ID Types**: Ensures ID-like values conform to expected formats (e.g., 24-char hex strings for Mongo).
- **Flexible API**:
  - **Static**: `ResultNormalizer::normalize($row)`, `ResultNormalizer::normalizeAll($rows)`
  - **Fluent**: `ResultNormalizer::create()->recursive()->normalizeRow($row)`

## Usage

### Static Usage (Default Behavior)
```php
use Maatify\DataRepository\Generic\Support\ResultNormalizer;

// Single row normalization
$row = $repository->find($id);
$normalizedRow = ResultNormalizer::normalize($row);

// Result set normalization
$rows = $repository->findBy($filters);
$normalizedRows = ResultNormalizer::normalizeAll($rows);
```

### Fluent / Configured Usage
```php
use Maatify\DataRepository\Generic\Support\ResultNormalizer;

$normalizer = ResultNormalizer::create()
    ->keepMongoId(true)       // Keep _id field even if mapped to id
    ->recursive(true)         // Normalize nested arrays
    ->strictIdTypes(true);    // Enforce strict ID checks

$row = $normalizer->normalizeRow($rawData);
$rows = $normalizer->normalizeBatch($rawDataList);
```

## Next Steps
- Integrate `ResultNormalizer` into the `BaseRepository` or `Generic` implementations (e.g., in `GenericMongoRepository::toArray`).
- Utilize the recursive capability for complex document structures.
