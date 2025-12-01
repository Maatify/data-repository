<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed
 * @since       2025-11-25
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic\Support;

use InvalidArgumentException;

final class FilterUtils
{
    /** @var array<int,string> */
    private const array ALLOWED_OPERATORS = [
        '>', '<', '>=', '<=', '!=', '<>', 'LIKE',
        'IN', 'NOT IN', 'BETWEEN', 'IS NULL', 'IS NOT NULL',
    ];

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

        /** @var array<int,string> $clauses */
        $clauses = [];

        /** @var array<string,mixed> $params */
        $params  = [];

        foreach ($filters as $col => $val) {
            if (! self::isValidSqlColumn($col)) {
                throw new InvalidArgumentException("Invalid SQL column name '{$col}'");
            }

            $base = $col;

            if (!is_array($val)) {
                if ($val === null) {
                    $clauses[] = "`{$col}` IS NULL";
                } else {
                    $clauses[] = "`{$col}` = :{$base}";
                    $params[$base] = $val;
                }

            } else {
                /** @var array<int,string> $opClauses */
                $opClauses = self::processSqlOperators($col, $val, $base, $params);
                $clauses = array_merge($clauses, $opClauses);
            }
        }

        if ($clauses === []) {
            /** @var array<string,mixed> $emptyParams */
            $emptyParams = [];
            return ['', $emptyParams];
        }

        return [' WHERE ' . implode(' AND ', $clauses), $params];
    }

    /**
     * @param string $col
     * @param array<array-key,mixed> $ops
     * @param string $base
     * @param array<string,mixed> $params
     * @return array<int,string>
     */
    private static function processSqlOperators(
        string $col,
        array $ops,
        string $base,
        array &$params
    ): array {
        /** @var array<int,string> $clauses */
        $clauses = [];

        foreach ($ops as $op => $value) {
            $op = strtoupper((string)$op);

            if (!in_array($op, self::ALLOWED_OPERATORS, true)) {
                throw new InvalidArgumentException("Unsupported SQL operator '{$op}'");
            }

            $clause = self::buildSqlOperatorClause($col, $op, $value, $base, $params);
            $clauses[] = $clause;
        }

        return $clauses;
    }

    /**
     * @param array<string,mixed> $params
     */
    private static function buildSqlOperatorClause(
        string $col,
        string $op,
        mixed $value,
        string $base,
        array &$params
    ): string {
        $suffix = str_replace(['>', '<', '=', '!', ' '], ['GT','LT','EQ','NE','_'], $op);
        $p = "{$base}_{$suffix}";

        switch ($op) {
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
        /** @var array<string,mixed> $final */
        $final = [];

        foreach ($filters as $field => $value) {
            $field = $field === 'id' ? '_id' : $field;

            if (! self::isValidMongoField($field)) {
                throw new InvalidArgumentException("Invalid Mongo field '{$field}'");
            }

            if (!is_array($value)) {
                $final[$field] = $value;
                continue;
            }

            /** @var array<string,mixed> $conditions */
            $conditions = self::processMongoOperators($field, $value);

            // merge conditions into final
            foreach ($conditions as $f => $ops) {

                if (! isset($final[$f])) {
                    $final[$f] = $ops;
                    continue;
                }

                if (is_array($final[$f]) && is_array($ops)) {
                    /** @var array<string,mixed> $merged */
                    $merged = array_merge($final[$f], $ops);
                    $final[$f] = $merged;
                    continue;
                }

                // fallback overwrite case
                $final[$f] = $ops;
            }
        }

        return $final;
    }

    /**
     * @param array<array-key,mixed> $ops
     * @return array<string,mixed>
     */
    private static function processMongoOperators(string $field, array $ops): array
    {
        /** @var array<string,mixed> $resultOps */
        $resultOps = [];

        foreach ($ops as $op => $value) {
            $op = strtoupper((string)$op);

            if (!in_array($op, self::ALLOWED_OPERATORS, true)) {
                throw new InvalidArgumentException("Unsupported Mongo operator '{$op}'");
            }

            /** @var array<string,mixed> $condition */
            $condition = self::buildMongoOperatorCondition($field, $op, $value);

            if (! isset($resultOps[$field])) {
                $resultOps[$field] = $condition[$field];
                continue;
            }

            if (is_array($resultOps[$field]) && is_array($condition[$field])) {
                /** @var array<string,mixed> $merged */
                $merged = array_merge($resultOps[$field], $condition[$field]);
                $resultOps[$field] = $merged;
            } else {
                $resultOps[$field] = $condition[$field];
            }
        }

        return [$field => $resultOps[$field]];
    }

    /**
     * @return array<string,mixed>
     */
    private static function buildMongoOperatorCondition(string $field, string $op, mixed $value): array
    {
        switch ($op) {
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

        /** @var array<string,mixed> $empty */
        $empty = [];
        return [$field => $empty];
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
