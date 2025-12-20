<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-19
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic\Support\Safety;

/**
 * Configuration for Redis Safety Guards (Phase 4).
 *
 * Defines hard limits for Redis operations to prevent memory exhaustion and blocking.
 */
class RedisSafetyConfig
{
    /**
     * Maximum number of keys allowed to be scanned/collected in a single operation.
     * Default: 5000 (ADR-006)
     */
    private int $maxScanKeys;

    /**
     * Maximum number of SCAN iterations allowed.
     * Prevents infinite loops or excessive latency on massive datasets.
     * Default: 1000
     */
    private int $maxScanIterations;

    public function __construct(int $maxScanKeys = 5000, int $maxScanIterations = 1000)
    {
        $this->maxScanKeys = $maxScanKeys;
        $this->maxScanIterations = $maxScanIterations;
    }

    public function getMaxScanKeys(): int
    {
        return $this->maxScanKeys;
    }

    public function getMaxScanIterations(): int
    {
        return $this->maxScanIterations;
    }
}
