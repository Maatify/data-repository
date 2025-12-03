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

final class FilterParser
{
    /** @var array<int,string> */
    public const array ALLOWED_OPERATORS = [
        '>', '<', '>=', '<=', '!=', '<>', 'LIKE',
        'IN', 'NOT IN', 'BETWEEN', 'IS NULL', 'IS NOT NULL',
    ];

    /**
     * @param array<array-key,mixed> $filters
     * @return FieldFilter[]
     */
    public function parse(array $filters): array
    {
        /** @var FieldFilter[] $result */
        $result = [];

        foreach ($filters as $field => $value) {
            $field = (string)$field;

            if (! is_array($value)) {
                // Short syntax: ['status' => 1] => field = value
                if ($value === null) {
                    $result[] = new FieldFilter($field, 'IS NULL', null);
                } else {
                    $result[] = new FieldFilter($field, '=', $value);
                }
                continue;
            }

            // Operator syntax: ['age' => ['>' => 18, '<' => 30]]
            foreach ($value as $op => $val) {
                $op = strtoupper((string)$op);

                if (! in_array($op, self::ALLOWED_OPERATORS, true)) {
                    throw new InvalidArgumentException("Unsupported operator '{$op}'");
                }

                $result[] = new FieldFilter($field, $op, $val);
            }
        }

        return $result;
    }
}
