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
 *
 * Today it mainly wraps a PDO instance (or a PDO‑compatible fake),
 * but it is kept generic so it can be extended later if needed.
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
}
