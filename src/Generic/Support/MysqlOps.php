<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 02:35
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic\Support;

use PDO;

/**
 * 🔌 MysqlOps
 *
 * Small normalization wrapper for low–level MySQL drivers.
 * Provides unified methods for common operations like ID retrieval.
 */
final class MysqlOps
{
    /**
     * @var PDO|object
     */
    private object $driver;

    /**
     * @param PDO|object $driver PDO instance or a PDO‑compatible fake/adapter
     */
    public function __construct(object $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Expose raw driver for advanced usages.
     *
     * @return PDO|object
     */
    public function getDriver(): object
    {
        return $this->driver;
    }

    /**
     * Normalize last insert ID retrieval.
     * Handles different driver return types (string/false/int).
     *
     * @return int|string
     */
    public function lastInsertId(): int|string
    {
        if ($this->driver instanceof PDO) {
            $id = $this->driver->lastInsertId();
            if ($id === false) {
                return 0;
            }
            // PDO returns string for IDs usually, but can be int if driver handles it.
            // Check if numeric string
            if (is_numeric($id)) {
                // Handle 64-bit integer strings safely
                if ((string)(int)$id === (string)$id) {
                    return (int)$id;
                }
                // Return as string if it overflows integer bounds
                return (string)$id;
            }
            return $id;
        }

        // For Fakes/Other drivers
        if (method_exists($this->driver, 'lastInsertId')) {
            /** @var mixed $id */
            $id = $this->driver->lastInsertId();
            if ($id === false) {
                return 0;
            }
            if (is_int($id)) {
                return $id;
            }
            if (is_string($id)) {
                if (is_numeric($id)) {
                    if ((string)(int)$id === $id) {
                        return (int)$id;
                    }
                }
                return $id;
            }
        }

        return 0;
    }
}
