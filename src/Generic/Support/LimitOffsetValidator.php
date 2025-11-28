<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-28 23:15
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic\Support;

use Maatify\DataRepository\Exceptions\RepositoryException;

class LimitOffsetValidator
{
    public const MAX_LIMIT  = 10000;
    public const MAX_OFFSET = 100000;

    /**
     * @throws RepositoryException
     */
    public static function validate(?int $limit, ?int $offset): void
    {
        self::validateLimit($limit);
        self::validateOffset($offset);
    }

    /**
     * @throws RepositoryException
     */
    private static function validateLimit(?int $limit): void
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

        if ($limit > self::MAX_LIMIT) {
            throw new RepositoryException(sprintf(
                'Limit %d exceeds the maximum allowed (%d). Consider paging your query.',
                $limit,
                self::MAX_LIMIT
            ));
        }
    }


    /**
     * @throws RepositoryException
     */
    private static function validateOffset(?int $offset): void
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

        if ($offset > self::MAX_OFFSET) {
            throw new RepositoryException(sprintf(
                'Offset cannot exceed %d. Given: %d',
                self::MAX_OFFSET,
                $offset
            ));
        }
    }

    /**
     * Normalize for safe query building.
     *
     * @return array{limit: int, offset: int}
     */
    public static function normalize(?int $limit, ?int $offset): array
    {
        return [
            'limit' => max(0, min($limit ?? 0, self::MAX_LIMIT)),
            'offset' => max(0, min($offset ?? 0, self::MAX_OFFSET)),
        ];
    }

    /**
     * Validate and normalize in one call for convenience.
     *
     * @throws RepositoryException
     * @return array{limit: int, offset: int}
     */
    public static function validateAndNormalize(?int $limit, ?int $offset): array
    {
        self::validate($limit, $offset);

        return self::normalize($limit, $offset);
    }



}
