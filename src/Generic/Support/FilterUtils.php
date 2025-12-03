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

final class FilterUtils
{
    /** @var array<string,string> */
    private const array SQL_TO_MONGO = [
        '>'      => '$gt',
        '<'      => '$lt',
        '>='     => '$gte',
        '<='     => '$lte',
        '!='     => '$ne',
        '<>'     => '$ne',
        'IN'     => '$in',
        'NOT IN' => '$nin',
        'LIKE'   => '$regex',
    ];

    /** @var array<int,string> */
    private const array SQL_RESERVED = [
        'SELECT','INSERT','UPDATE','DELETE','DROP',
        'CREATE','ALTER','TABLE','FROM',
        'WHERE','JOIN','UNION','OR','AND','NOT',
    ];

    /** @var array<int,string> */
    private const array MONGO_RESERVED = [
        '$where', '$group', '$match',
    ];

    /**
     * @param array<array-key,mixed> $filters
     * @return array{0:string,1:array<string,mixed>}
     */
    public static function buildSqlWhere(array $filters): array
    {
        if ($filters === []) {
            /** @var array<string,mixed> $empty */
            $empty = [];
            return ['', $empty];
        }

        $parser = new FilterParser();
        $conditions = $parser->parse($filters);

        /** @var array<int,string> $clauses */
        $clauses = [];

        /** @var array<string,mixed> $params */
        $params  = [];

        foreach ($conditions as $filter) {
            $colName = $filter->field;

            if (! self::isValidSqlColumn($colName)) {
                throw new InvalidArgumentException("Invalid SQL column name '{$colName}'");
            }

            $base = $colName;
            $op = $filter->operator;
            $val = $filter->value;

            $suffix = str_replace(['>', '<', '=', '!', ' '], ['GT','LT','EQ','NE','_'], $op);
            $p = "{$base}_{$suffix}";

            // Handle uniqueness of parameters for same field/operator if needed,
            // but for now relying on FilterParser output structure which maps roughly to original.
            // Note: If multiple filters for same field with same operator exist, this might overwrite.
            // However, array input `['age' => ['>' => 18]]` prevents duplicate operators for same field.
            // So uniqueness is guaranteed by input array structure.

            $clauses[] = self::buildSqlClause($colName, $op, $val, $base, $params, $p);
        }

        if ($clauses === []) {
            /** @var array<string,mixed> $emptyParams */
            $emptyParams = [];
            return ['', $emptyParams];
        }

        return [' WHERE ' . implode(' AND ', $clauses), $params];
    }

    /**
     * @param array<string,mixed> $params
     */
    private static function buildSqlClause(
        string $col,
        string $op,
        mixed $value,
        string $base,
        array &$params,
        string $p
    ): string {
        switch ($op) {
            case '=':
                $params[$base] = $value;
                return "`{$col}` = :{$base}";

            case 'IN':
            case 'NOT IN':
                if (!is_array($value)) {
                    throw new InvalidArgumentException("'{$op}' requires array");
                }

                if ($value === []) {
                    return $op === 'IN' ? '1=0' : '1=1';
                }

                /** @var array<int,string> $place */
                $place = [];

                foreach (array_values($value) as $i => $v) {
                    $pi = "{$p}_{$i}";
                    $place[] = ":{$pi}";
                    $params[$pi] = $v;
                }

                return "`{$col}` {$op} (" . implode(', ', $place) . ')';

            case 'LIKE':
                if (!is_string($value)) {
                    throw new InvalidArgumentException('LIKE requires string');
                }
                $params[$p] = $value;
                return "`{$col}` LIKE :{$p}";

            case 'BETWEEN':
                if (!is_array($value) || count($value) !== 2) {
                    throw new InvalidArgumentException('BETWEEN requires [v1,v2]');
                }

                $p1 = "{$p}_1";
                $p2 = "{$p}_2";

                $params[$p1] = $value[0];
                $params[$p2] = $value[1];

                return "`{$col}` BETWEEN :{$p1} AND :{$p2}";

            case 'IS NULL':
                return "`{$col}` IS NULL";

            case 'IS NOT NULL':
                return "`{$col}` IS NOT NULL";

            default:
                $params[$p] = $value;
                return "`{$col}` {$op} :{$p}";
        }
    }

    /**
     * @param array<array-key,mixed> $filters
     * @return array<string|int, mixed>
     */
    public static function buildMongoFilter(array $filters): array
    {
        $parser = new FilterParser();
        $conditions = $parser->parse($filters);

        /** @var array<string,mixed> $final */
        $final = [];

        foreach ($conditions as $filter) {
            $field = $filter->field === 'id' ? '_id' : $filter->field;

            if (! self::isValidMongoField($field)) {
                throw new InvalidArgumentException("Invalid Mongo field '{$field}'");
            }

            /** @var array<string,mixed> $condition */
            $condition = self::buildMongoCondition($field, $filter->operator, $filter->value);

            // Merge logic
            if (! isset($final[$field])) {
                $final[$field] = $condition[$field];
                continue;
            }

            if (is_array($final[$field]) && is_array($condition[$field])) {
                /** @var array<string,mixed> $merged */
                $merged = array_merge($final[$field], $condition[$field]);
                $final[$field] = $merged;
            } else {
                // If existing value is scalar (equality), and new is also scalar, overwrite.
                // Or if one is scalar and other is operator array, we probably need to handle more gracefully.
                // But simplified logic: overwrite or merge if arrays.
                $final[$field] = $condition[$field];
            }
        }

        return $final;
    }

    /**
     * @return array<string,mixed>
     */
    private static function buildMongoCondition(string $field, string $op, mixed $value): array
    {
        switch ($op) {
            case '=':
                return [$field => $value];

            case 'LIKE':
                if (!is_string($value)) {
                    throw new InvalidArgumentException('LIKE requires string');
                }

                $pattern = preg_quote($value, '/');
                $pattern = str_replace(['%','_'], ['.*','.'], $pattern);

                return [
                    $field => [
                        '$regex'   => $pattern,
                        '$options' => 'i',
                    ],
                ];

            case 'BETWEEN':
                if (!is_array($value) || count($value) !== 2) {
                    throw new InvalidArgumentException('BETWEEN requires array[2]');
                }
                return [
                    $field => [
                        '$gte' => $value[0],
                        '$lte' => $value[1],
                    ],
                ];

            case 'IS NULL':
                return [$field => null];

            case 'IS NOT NULL':
                return [$field => ['$ne' => null]];

            case 'IN':
            case 'NOT IN':
                if (!is_array($value)) {
                    throw new InvalidArgumentException("'{$op}' requires array");
                }

                return [
                    $field => [
                        $op === 'IN' ? '$in' : '$nin' => array_values($value),
                    ],
                ];

            default:
                if (isset(self::SQL_TO_MONGO[$op])) {
                    return [
                        $field => [
                            self::SQL_TO_MONGO[$op] => $value,
                        ],
                    ];
                }
        }

        // Should not happen as parser validates operators
        return [$field => []];
    }

    private static function isValidSqlColumn(string $name): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name) === 1
               && ! in_array(strtoupper($name), self::SQL_RESERVED, true);
    }

    private static function isValidMongoField(string $name): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name) === 1
               && ! in_array(strtolower($name), self::MONGO_RESERVED, true);
    }
}
