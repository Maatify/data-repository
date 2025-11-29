# Phase 13: AutoCasting System

## Status: Completed
## Version: 1.0.3

## Summary
The **AutoCasting System** introduces a robust, declarative way to transform raw data types (strings from databases or APIs) into PHP strict types (`int`, `bool`, `float`, `DateTimeImmutable`, `array`) within the hydration pipeline.

## Changes
- **Added `Maatify\DataRepository\Hydration\AutoCaster`**: A static utility class handling type conversion.
- **Updated `Maatify\DataRepository\Hydration\BaseHydrator`**: Integrated `AutoCaster` into the `onCast()` stage.
- **Added Integration**: Concrete hydrators can now override `getCastingDefinitions()` to define rules without writing manual casting logic.

## Usage

### Defining Casting Rules in a Hydrator
Override `getCastingDefinitions` in your hydrator class:

```php
protected function getCastingDefinitions(): array
{
    return [
        'id'          => AutoCaster::TYPE_INT,
        'price'       => AutoCaster::TYPE_FLOAT,
        'is_active'   => AutoCaster::TYPE_BOOL,
        'created_at'  => AutoCaster::TYPE_DATETIME,
        'metadata'    => AutoCaster::TYPE_JSON,
    ];
}
```

### Supported Types
- `AutoCaster::TYPE_INT` (`'int'`)
- `AutoCaster::TYPE_FLOAT` (`'float'`)
- `AutoCaster::TYPE_BOOL` (`'bool'`)
- `AutoCaster::TYPE_STRING` (`'string'`)
- `AutoCaster::TYPE_DATETIME` (`'datetime'`) - Parses strings or timestamps into `DateTimeImmutable`.
- `AutoCaster::TYPE_JSON` (`'json'`) - Decodes JSON strings into arrays; returns empty array on failure.

## Tests
- `tests/Hydration/AutoCasterTest.php`: Unit tests for all casting types and edge cases (invalid JSON, nulls).
- `tests/Hydration/BaseHydratorCastingTest.php`: Integration test verifying the `BaseHydrator` pipeline correctly applies definitions.
