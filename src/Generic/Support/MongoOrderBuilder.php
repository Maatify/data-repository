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

final class MongoOrderBuilder
{
    /**
     * Builds a MongoDB sort array.
     *
     * @param array<string, string>|null $orderBy
     * @return array<string, int>
     */
    public function build(?array $orderBy): array
    {
        $normalized = OrderUtils::normalize($orderBy);

        if (empty($normalized)) {
            return [];
        }

        $sort = [];

        foreach ($normalized as $column => $direction) {
            $sort[$column] = $direction === OrderUtils::ORDER_ASC ? 1 : -1;
        }

        return $sort;
    }
}
