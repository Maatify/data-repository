# Phase 20: SQL & Filter Improvements

## Summary
Improved the debuggability of generated SQL by introducing semantic placeholders and enhanced the robustness of MySQL integer handling for 64-bit environments.

## Changes

### 1. Semantic Placeholders in `FilterUtils`
*   **Refactored** `FilterUtils::buildSqlWhere` to use column names as the base for placeholders (e.g., `:status`, `:age_GT`) instead of generic numbered keys (`:p0`, `:p1_GT`).
*   **Benefits:**
    *   Generated SQL is much easier to read and debug.
    *   Parameter arrays are self-documenting.

### 2. Robust Integer Handling in `MysqlOps`
*   **Refactored** `MysqlOps::lastInsertId` to safely handle large integers.
*   **Logic:**
    *   Checks if the returned ID is a numeric string.
    *   Verifies if casting to `int` and back to `string` preserves the value (ensuring no overflow/truncation).
    *   Returns `int` if safe, otherwise preserves the original `string` (e.g., for BIGINTs larger than `PHP_INT_MAX` or on 32-bit systems).

## Tests
*   `tests/Generic/SQL/SemanticPlaceholdersTest.php`: Verifies that generated SQL uses expected semantic placeholders.
*   `tests/Generic/SQL/BigIntTest.php`: Verifies `lastInsertId` behavior with small ints, huge ints (as strings), and boundary cases.

## Outputs
*   `src/Generic/Support/FilterUtils.php` (Modified)
*   `src/Generic/Support/MysqlOps.php` (Modified)
*   `tests/Generic/SQL/SemanticPlaceholdersTest.php` (New)
*   `tests/Generic/SQL/BigIntTest.php` (New)
