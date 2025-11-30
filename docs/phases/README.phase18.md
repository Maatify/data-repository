# Phase 18: Integration With data-fakes + data-integrations

## Overview
This phase focuses on ensuring that the Generic Repositories behave identically whether they are backed by "Fake" adapters (in-memory arrays) or "Real" adapters (actual databases).

## Changes
- **Tests**:
    - Created `tests/Integration/IntegrationValidatorTest.php` as a base for integration verification.
    - Created `tests/Integration/FakeVsRealMatrixTest.php` to run a matrix of tests across all supported drivers.
- **Verification**:
    - The tests currently run against Mocks/Fakes to validate the API surface and contract.
    - The structure is in place to plug in Real adapters (via `tests/Helpers/RealAdapterTrait.php`) when the environment permits.

## Key Concepts
- **Matrix Testing**: Running the same test logic against multiple implementations to ensure polymorphism.
- **Integration Validator**: A shared test harness that asserts outcome parity.

## Usage
To add a new adapter to the matrix:
1. Open `tests/Integration/FakeVsRealMatrixTest.php`.
2. Add the adapter instance to the `adapterProvider` array.
3. Ensure the adapter implements the standard CRUD interface.
