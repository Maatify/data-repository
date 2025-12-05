# Phase 27: NormalizerOptions + LimitOffsetConfig

## Overview
Phase 27 introduces strict value objects (`NormalizerOptions`, `LimitOffsetConfig`) to configure `ResultNormalizer` and `LimitOffsetValidator` flexibly at runtime. This allows per-query customization of normalization behavior (e.g., recursive handling, ID types) and pagination constraints (limit/offset bounds) without modifying global state or class constants.

## Changes

### New Classes
1.  **`Maatify\DataRepository\Generic\Support\NormalizerOptions`**
    *   Immutable Value Object.
    *   Configures:
        *   `keepMongoId` (bool): Whether to preserve `_id` in output.
        *   `recursive` (bool): Whether to normalize nested arrays.
        *   `strictIdTypes` (bool): Whether to validate ID formats strictly.

2.  **`Maatify\DataRepository\Generic\Pagination\LimitOffsetConfig`**
    *   Immutable Value Object.
    *   Configures:
        *   `maxLimit` (int): Maximum allowed limit per query.
        *   `maxOffset` (int): Maximum allowed offset.

### Modified Components
*   **`ResultNormalizer`**:
    *   Added `normalizeWithOptions()` and `normalizeAllWithOptions()` static methods.
    *   Added `fromOptions()` factory method.
    *   Maintains backward compatibility with boolean constructor arguments.

*   **`LimitOffsetValidator`**:
    *   Added `validateWithConfig()` and `validateAndNormalize()` accepting `LimitOffsetConfig`.
    *   Allows overriding default `MAX_LIMIT` (10,000) and `MAX_OFFSET` (100,000) for specific contexts (e.g., admin exports).

## Usage Examples

### Using NormalizerOptions
```php
use Maatify\DataRepository\Generic\Support\NormalizerOptions;
use Maatify\DataRepository\Generic\Support\ResultNormalizer;

$options = NormalizerOptions::create()
    ->withKeepMongoId(true)
    ->withRecursive(true);

$data = ['_id' => 123, 'info' => ['status' => 'active']];
$result = ResultNormalizer::normalizeWithOptions($data, $options);
```

### Using LimitOffsetConfig
```php
use Maatify\DataRepository\Generic\Pagination\LimitOffsetConfig;
use Maatify\DataRepository\Generic\Support\LimitOffsetValidator;

$config = LimitOffsetConfig::create()
    ->withMaxLimit(50000)  // Allow larger export
    ->withMaxOffset(200000);

// Validates against custom bounds
LimitOffsetValidator::validateWithConfig($limit, $offset, $config);
```

## Testing
*   Added `tests/Generic/Normalization/NormalizerOptionsTest.php`
*   Added `tests/Generic/Pagination/LimitOffsetConfigTest.php`
*   Verified immutability and integration with services.
