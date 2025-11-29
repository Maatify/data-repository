# Phase 11: HydratorInterface + Pipeline Contract

## Summary
This phase introduces the contract for the hydration layer, which transforms raw data (arrays) into domain objects. It defines the `HydratorInterface` and the `HydrationContext`, establishing the foundation for a configurable, multi-stage pipeline.

## Changes

### Added
- **`Maatify\DataRepository\Hydration\HydratorInterface`**: The contract for all hydrators, supporting single (`hydrate`) and bulk (`hydrateAll`) operations.
- **`Maatify\DataRepository\Hydration\HydrationContext`**: A state object passed through the hydration pipeline, carrying metadata (e.g., locale, user info) and defining pipeline stages.

## Usage

### Implementing the Interface

```php
use Maatify\DataRepository\Hydration\HydratorInterface;
use Maatify\DataRepository\Hydration\HydrationContext;

class UserHydrator implements HydratorInterface
{
    public function hydrate(array $data, ?HydrationContext $context = null): object
    {
        // ... transform array to User object
        return new User($data['id'], $data['name']);
    }

    public function hydrateAll(array $dataset, ?HydrationContext $context = null): array
    {
        $results = [];
        foreach ($dataset as $data) {
            $results[] = $this->hydrate($data, $context);
        }
        return $results;
    }
}
```

### Using Context

```php
$context = new HydrationContext();
$context->addMeta('locale', 'en_US');

// Access constants for stages
echo HydrationContext::STAGE_CAST; // 'cast'
```
