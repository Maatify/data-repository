# Phase 14: DTO Mapping + Hydration Profiles
[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](../../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

## Overview
Phase 14 enhances the hydration system by introducing `MappingProfile` for defining mapping rules, transformers for value conversion, and integrating these capabilities into the Generic Repository layer.

## Features

### 1. Mapping Profiles
*   **Declarative Mapping:** Define how source data keys map to destination object properties.
*   **Default Values:** Set default values for missing fields.
*   **Transformers:** Apply transformations to specific fields (e.g., JSON string to array, string to DateTime).

### 2. Transformers
*   **`TransformerInterface`:** Contract for value transformers.
*   **`JsonTransformer`:** Decodes JSON strings or verifies arrays.
*   **`DateTimeTransformer`:** Converts strings or timestamps to `DateTimeImmutable`.

### 3. Repository Integration
*   **`RepositoryHydrationTrait`:** Provides `findObject()` and `findObjectsBy()` methods to Generic Repositories.
*   **`BaseRepository`:** Added `setHydrator()` and `getHydrator()` methods.
*   **Generic Repositories:** Updated MySQL, Mongo, and Redis generic repositories to include the hydration trait.

## Usage

### Defining a Profile
```php
use Maatify\DataRepository\Hydration\MappingProfile;
use Maatify\DataRepository\Hydration\Transformers\DateTimeTransformer;

$profile = new MappingProfile();
$profile->addMap('db_column_name', 'propertyNames')
        ->addDefault('status', 'active')
        ->addTransformer('created_at', new DateTimeTransformer());
```

### Using in a Repository
```php
$repo = new MyUserRepository($adapter);
$repo->setHydrator(new UserHydrator());

// Returns UserDto object instead of array
$userDto = $repo->findObject(123);
```

## Files Created
*   `src/Hydration/TransformerInterface.php`
*   `src/Hydration/MappingProfile.php`
*   `src/Hydration/Transformers/JsonTransformer.php`
*   `src/Hydration/Transformers/DateTimeTransformer.php`
*   `src/Generic/Support/RepositoryHydrationTrait.php`
*   `tests/Hydration/DTO/*`
*   `tests/Generic/Hydration/*`

## Verification
*   Tests in `tests/Hydration/DTO/` verify profile configuration and transformer logic.
*   Tests in `tests/Generic/Hydration/` verify repository integration.
