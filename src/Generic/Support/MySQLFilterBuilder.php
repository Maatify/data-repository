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

final class MySQLFilterBuilder
{
    /** @var array<int,string> */
    private const array SQL_RESERVED = [
        'SELECT','INSERT','UPDATE','DELETE','DROP',
        'CREATE','ALTER','TABLE','FROM',
        'WHERE','JOIN','UNION','OR','AND','NOT',
    ];

    /**
     * @param array<array-key,mixed> $filters
     * @return array{0:string,1:array<string,mixed>}
     */
    public function build(array $filters): array
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

            if (! $this->isValidSqlColumn($colName)) {
                throw new InvalidArgumentException("Invalid SQL column name '{$colName}'");
            }

            $base = $colName;
            $op = $filter->operator;
            $val = $filter->value;

            $suffix = str_replace(['>', '<', '=', '!', ' '], ['GT','LT','EQ','NE','_'], $op);
            $p = "{$base}_{$suffix}";

            $clauses[] = $this->buildSqlClause($colName, $op, $val, $base, $params, $p);
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
    private function buildSqlClause(
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

    private function isValidSqlColumn(string $name): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name) === 1
            && ! in_array(strtoupper($name), self::SQL_RESERVED, true);
    }
}
