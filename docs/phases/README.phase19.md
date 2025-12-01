# Phase 19: NoSQL Robustness (Mongo & Redis)

## Overview
This phase enhances the robustness of the Generic Mongo and Redis repositories. It addresses the inconsistency in Mongo collection naming (ensuring dynamic switching via `setCollectionName` is possible and robust) and adds in-memory filtering capabilities to the Generic Redis Repository, allowing `findBy`, `findOneBy`, and `paginateBy` to work by iterating over keys (useful for small datasets or specialized use cases).

## Changes

### Mongo
- **`GenericMongoRepository`**:
    - Added `setCollectionName(string $name)` to explicitly set the collection.
    - Updated `getCollectionObj()` to prioritize the explicitly set collection name while preserving the fallback to `tableName` for backward compatibility. This fixes issues where `tableName` might have permanently overwritten `collectionName`.

### Redis
- **`GenericRedisRepository`**:
    - Implemented `findBy(array $filters)` using an "in-memory scan" strategy:
        1. Fetches all records via `findAll` (or keys matching prefix).
        2. Filters the records in PHP memory using a simple matching logic (Equality and List-IN support).
        3. Applies sorting via `OrderUtils`.
        4. Applies Limit/Offset slicing.
    - Implemented `findOneBy(array $filters)` wrapping `findBy`.
    - Implemented `paginateBy(array $filters)` to support paginated filtered results.
    - Added private helper `matches(array $item, array $filters)` to handle row matching.

### Tests
- **`tests/Generic/NoSQL/MongoRobustnessTest.php`**: Verified collection naming logic using mocked adapters.
- **`tests/Generic/NoSQL/RedisFilteringTest.php`**: Verified `findBy`, `findOneBy`, `paginateBy`, sorting, and limit/offset logic using an in-memory Fake Redis driver.

## Notes
- The Redis `findBy` implementation performs a full scan of keys matching the prefix. This is **not efficient for large datasets** and should be used with caution in production. It is primarily intended for smaller lookup tables or caching layers where secondary indexing is needed without full RediSearch implementation.
