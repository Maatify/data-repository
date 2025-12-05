<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-28 23:15
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic\Support;

use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\Pagination\LimitOffsetConfig;

class LimitOffsetValidator
{
    public const int MAX_LIMIT  = 10000;
    public const int MAX_OFFSET = 100000;

    /**
     * @throws RepositoryException
     */
    public static function validate(?int $limit, ?int $offset): void
    {
        self::validateWithConfig($limit, $offset);
    }

    /**
     * @throws RepositoryException
     */
    public static function validateWithConfig(?int $limit, ?int $offset, ?LimitOffsetConfig $config = null): void
    {
        $maxLimit = $config ? $config->getMaxLimit() : self::MAX_LIMIT;
        $maxOffset = $config ? $config->getMaxOffset() : self::MAX_OFFSET;

        self::validateLimit($limit, $maxLimit);
        self::validateOffset($offset, $maxOffset);
    }

    /**
     * @throws RepositoryException
     */
    private static function validateLimit(?int $limit, int $maxLimit): void
    {
        if ($limit === null) {
            return;
        }

        if ($limit < 1) {
            throw new RepositoryException(sprintf(
                'Invalid limit value: %d. Limit must be >= 1. Hint: Use null for unlimited results.',
                $limit
            ));
        }

        if ($limit > $maxLimit) {
            throw new RepositoryException(sprintf(
                'Limit %d exceeds the maximum allowed (%d). Consider paging your query.',
                $limit,
                $maxLimit
            ));
        }
    }

    /**
     * @throws RepositoryException
     */
    private static function validateOffset(?int $offset, int $maxOffset): void
    {
        if ($offset === null) {
            return;
        }

        if ($offset < 0) {
            throw new RepositoryException(sprintf(
                'Offset must be >= 0. Given: %d',
                $offset
            ));
        }

        if ($offset > $maxOffset) {
            throw new RepositoryException(sprintf(
                'Offset cannot exceed %d. Given: %d',
                $maxOffset,
                $offset
            ));
        }
    }

    /**
     * Normalize for safe query building.
     *
     * @return array{limit: int, offset: int}
     */
    private static function normalize(?int $limit, ?int $offset, int $maxLimit, int $maxOffset): array
    {
        return [
            'limit' => max(0, min($limit ?? 0, $maxLimit)),
            'offset' => max(0, min($offset ?? 0, $maxOffset)),
        ];
    }

    /**
     * Validate and normalize in one call for convenience.
     *
     * @throws RepositoryException
     * @return array{limit: int, offset: int}
     */
    public static function validateAndNormalize(?int $limit, ?int $offset, ?LimitOffsetConfig $config = null): array
    {
        self::validateWithConfig($limit, $offset, $config);

        $maxLimit = $config ? $config->getMaxLimit() : self::MAX_LIMIT;
        $maxOffset = $config ? $config->getMaxOffset() : self::MAX_OFFSET;

        return self::normalize($limit, $offset, $maxLimit, $maxOffset);
    }
}
