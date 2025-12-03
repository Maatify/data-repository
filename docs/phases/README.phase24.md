# Phase 24: OrderParser Extraction

[![Maatify Repository](https://img.shields.io/badge/Maatify-Repository-blue?style=for-the-badge)](../../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

## Summary
In this phase, we extracted the order parsing logic into a dedicated `OrderParser` and introduced the `OrderField` value object. This simplifies `OrderUtils` and prepares the codebase for dedicated Order Builders in Phase 25.

## Changes
- **Added**: `Maatify\DataRepository\Generic\Support\OrderParser`
- **Added**: `Maatify\DataRepository\Generic\Support\OrderField`
- **Updated**: `Maatify\DataRepository\Generic\Support\OrderUtils` to use `OrderParser` internally.
- **Added**: `tests/Generic/Ordering/OrderParserTest.php` to verify parsing logic.

## Technical Details
The `OrderParser` takes an array of sort criteria (e.g., `['name' => 'ASC']`) and converts it into a list of `OrderField` objects. It handles:
- Validation of sort directions (ASC/DESC).
- Sanitization of column names.
- Fallback behavior for invalid directions (unless strict mode is enabled).

`OrderUtils::normalize()` now delegates to `OrderParser`, preserving backward compatibility while utilizing the new component.
