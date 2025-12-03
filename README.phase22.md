# Phase 22: FilterParser Extraction

## Summary
Extracted filtering logic parsing into a dedicated `FilterParser` class and introduced `FieldFilter` value object.
This refactoring separates the concern of parsing user input (filter arrays) from the concern of building SQL or Mongo queries.

## Changes
- **New Value Object**: `FieldFilter` represents a single parsed filter (field, operator, value).
- **New Service**: `FilterParser` converts raw filter arrays into a list of `FieldFilter` objects, handling validation of supported operators.
- **Refactoring**: `FilterUtils` now delegates parsing to `FilterParser` before generating SQL or Mongo structures.

## Benefits
- **Separation of Concerns**: Parsing logic is now independent of query generation.
- **Improved Testability**: `FilterParser` can be tested in isolation.
- **Preparation for Future**: This paves the way for driver-specific FilterBuilders (Phase 23).

## Usage
The `FilterParser` is primarily internal, but can be used directly if raw parsing is needed:

```php
use Maatify\DataRepository\Generic\Support\FilterParser;

$parser = new FilterParser();
$filters = $parser->parse([
    'status' => 1,
    'age'    => ['>' => 18, '<' => 30]
]);

// Returns array of FieldFilter objects
foreach ($filters as $filter) {
    echo $filter->field . ' ' . $filter->operator . ' ' . $filter->value;
}
```
