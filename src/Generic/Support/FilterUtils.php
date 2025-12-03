<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic\Support;

final class FilterUtils
{
    /**
     * @param array<array-key,mixed> $filters
     * @return array{0:string,1:array<string,mixed>}
     */
    public static function buildSqlWhere(array $filters): array
    {
        return (new MySQLFilterBuilder())->build($filters);
    }

    /**
     * @param array<array-key,mixed> $filters
     * @return array<string|int, mixed>
     */
    public static function buildMongoFilter(array $filters): array
    {
        return (new MongoFilterBuilder())->build($filters);
    }
}
