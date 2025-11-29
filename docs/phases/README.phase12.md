# Phase 12: BaseHydrator + Pipeline Execution

## Overview
This phase implements the `BaseHydrator`, an abstract class that orchestrates the hydration pipeline defined in Phase 11. It provides a flexible, stage-based execution model (Prepare -> Cast -> Map -> Validate -> Complete) allowing developers to inject custom logic at any step of the transformation process.

## Changes
- **Added** `src/Hydration/BaseHydrator.php`: The core abstract implementation of `HydratorInterface`.
- **Added** `tests/Hydration/BaseHydratorTest.php`: Verification of pipeline logic using anonymous classes.

## Architecture
The `BaseHydrator` uses a `HydrationContext` to determine which stages to execute.

### Pipeline Stages
1. **Prepare**: Normalize raw keys or clean values (e.g., `trim()`).
2. **Cast**: Convert primitive types (String -> Int, SQL Dates -> DateTime).
3. **Map**: Assign values to the target object properties.
4. **Validate**: Perform consistency checks on the populated object.
5. **Complete**: Finalize object state (e.g., set computed properties).

## Usage
Extend `BaseHydrator` and implement `createInstance()` and any stage hooks needed.

```php
class UserHydrator extends BaseHydrator
{
    protected function createInstance(): object
    {
        return new UserDTO();
    }

    protected function onPrepare(array $data): array
    {
        $data['email'] = strtolower($data['email']);
        return $data;
    }

    protected function onCast(array $data): array
    {
        $data['active'] = (bool)$data['active'];
        return $data;
    }
}
```
