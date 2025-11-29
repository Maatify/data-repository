<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 00:00:00
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\Bootstrap\Core\EnvironmentLoader;

/**
 * 🧩 **Environment Bootstrapping Script**
 *
 * 🎯 **Purpose: **
 * Provides a minimal executable test script to validate environment
 * loading functionality via {@see EnvironmentLoader}.
 *
 * 🧠 **Behavior: **
 * - Loads environment variables from the `.env` file located at the project root.
 * - Ensures that configuration values are correctly parsed and stored in `$_ENV`.
 * - Prints the currently active application environment (APP_ENV).
 *
 * ✅ **Usage: **
 * ```bash
 * php tests/bootstrap.php
 * ```
 * Expected output:
 * ```
 * 🧪 Environment: development
 * ```
 */

// ------------------------------------------------------------
// 1) Load composer autoload
// ------------------------------------------------------------
$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($autoload)) {
    fwrite(STDERR, "❌ Autoload not found: $autoload" . PHP_EOL);
    exit(1);
}

require_once $autoload;

// ------------------------------------------------------------
// 1.5) Load Stubs (if missing in environment)
// ------------------------------------------------------------
if (! class_exists('Maatify\Common\Pagination\DTO\PaginationDTO')) {
    $stubs = [
        __DIR__ . '/Stubs/Maatify/Common/Pagination/DTO/PaginationDTO.php',
        __DIR__ . '/Stubs/Maatify/Common/Pagination/DTO/PaginationResultDTO.php',
        __DIR__ . '/Stubs/Maatify/Common/Pagination/Helpers/PaginationHelper.php',
    ];

    foreach ($stubs as $stub) {
        if (file_exists($stub)) {
            require_once $stub;
        }
    }
}

// ------------------------------------------------------------
// 2) Load environment variables (testing/default)
// ------------------------------------------------------------
$loader = new EnvironmentLoader(dirname(__DIR__));
$loader->load();

// ------------------------------------------------------------
// 3) Normalize environment value for PHPStan level=max
// ------------------------------------------------------------
$envRaw = $_ENV['APP_ENV'] ?? 'unknown';

/*
 * PHPStan Safe Normalization
 * mixed → string (safe)
 */
$envString = is_scalar($envRaw)
    ? (string) $envRaw
    : 'unknown';

// ------------------------------------------------------------
// 4) Display current environment (deterministic, safe)
// ------------------------------------------------------------
echo '🧪 Environment: ' . $envString . PHP_EOL;

// ------------------------------------------------------------
// 5) Optional: Disable output buffering for CI
// ------------------------------------------------------------
if (function_exists('ini_set')) {
    ini_set('output_buffering', 'off');
    ini_set('implicit_flush', '1');
}
