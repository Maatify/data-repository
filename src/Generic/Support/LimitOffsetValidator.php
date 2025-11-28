<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 05:30
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic\Support;

use Maatify\DataRepository\Exceptions\RepositoryException;

class LimitOffsetValidator
{
    /**
     * @throws RepositoryException
     */
    public static function validate(?int $limit, ?int $offset): void
    {
        if ($limit !== null && $limit < 1) {
            throw new RepositoryException("Limit must be a positive integer.");
        }

        if ($offset !== null && $offset < 0) {
            throw new RepositoryException("Offset must be a non-negative integer.");
        }
    }
}
