# Phase 17: Paginated Hydrated Results
[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](../../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

## Overview
This phase integrates the Hydration System with the Pagination System. It enables `GenericRepositories` to return paginated results where the data items are fully hydrated objects (DTOs/Entities) instead of raw arrays, while preserving rich pagination metadata.

## Key Components

### `HydratedPaginationCollection`
A new DTO located in `Maatify\DataRepository\Pagination` that holds:
- `$data`: Array of hydrated objects.
- `$pagination`: `PaginationDTO` containing metadata (total, per_page, current_page, etc.).

### `RepositoryHydrationTrait` Updates
The trait has been enhanced with two new methods:
- `paginateObjects(int $page, int $perPage, ?array $orderBy)`
- `paginateObjectsBy(array $filters, int $page, int $perPage, ?array $orderBy)`

These methods internally call the repository's standard `paginateBy` method, hydrate the resulting data array using the configured `Hydrator`, and return a `HydratedPaginationCollection`.

## Usage Example

```php
$repository->setHydrator(new UserHydrator());

// Get page 1 with 20 items
$collection = $repository->paginateObjects(1, 20);

// Access hydrated objects
foreach ($collection->data as $user) {
    echo $user->name;
}

// Access metadata
echo $collection->pagination->total;
```

## Changes
- Added `src/Pagination/HydratedPaginationCollection.php`.
- Updated `src/Generic/Support/RepositoryHydrationTrait.php`.
- Added `tests/Pagination/Hydrated/HydratedPaginationTest.php`.
