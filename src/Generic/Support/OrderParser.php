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

use InvalidArgumentException;

final class OrderParser
{
    public const string ORDER_ASC = 'ASC';
    public const string ORDER_DESC = 'DESC';

    /**
     * @param array<string, string>|null $orderBy
     * @param bool $throwOnInvalid
     * @return OrderField[]
     */
    public function parse(?array $orderBy, bool $throwOnInvalid = false): array
    {
        if (empty($orderBy)) {
            return [];
        }

        $result = [];

        foreach ($orderBy as $column => $direction) {
            $column = (string)$column;
            $cleanColumn = preg_replace('/[^a-zA-Z0-9_.]/', '', $column);

            if ($cleanColumn === '' || $cleanColumn === null) {
                continue;
            }

            $upper = strtoupper((string)$direction);

            if ($upper !== self::ORDER_ASC && $upper !== self::ORDER_DESC) {
                if ($throwOnInvalid) {
                    throw new InvalidArgumentException(
                        "Invalid order direction: '{$direction}'. Must be 'ASC' or 'DESC'."
                    );
                }
                $upper = self::ORDER_ASC;
            }

            $result[] = new OrderField($cleanColumn, $upper);
        }

        return $result;
    }
}
