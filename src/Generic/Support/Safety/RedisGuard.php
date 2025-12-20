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

use Maatify\DataRepository\Exceptions\RedisSafetyException;

/**
 * Enforces Redis safety limits at runtime.
 */
class RedisGuard
{
    private RedisSafetyConfig $config;
    private int $scannedKeysCount = 0;
    private int $iterationsCount = 0;

    public function __construct(RedisSafetyConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Track a SCAN iteration and validate limits.
     *
     * @param int $newKeysFound Number of keys found in this iteration chunk.
     * @throws RedisSafetyException
     */
    public function trackScan(int $newKeysFound): void
    {
        $this->iterationsCount++;
        $this->scannedKeysCount += $newKeysFound;

        $this->validate();
    }

    /**
     * Reset counters for a new operation.
     */
    public function reset(): void
    {
        $this->scannedKeysCount = 0;
        $this->iterationsCount = 0;
    }

    /**
     * @throws RedisSafetyException
     */
    private function validate(): void
    {
        if ($this->iterationsCount > $this->config->getMaxScanIterations()) {
            throw new RedisSafetyException(
                sprintf(
                    'Redis safety limit exceeded: Max SCAN iterations (%d) reached.',
                    $this->config->getMaxScanIterations()
                )
            );
        }

        if ($this->scannedKeysCount > $this->config->getMaxScanKeys()) {
            throw new RedisSafetyException(
                sprintf(
                    'Redis safety limit exceeded: Max keys scanned (%d) reached.',
                    $this->config->getMaxScanKeys()
                )
            );
        }
    }
}
