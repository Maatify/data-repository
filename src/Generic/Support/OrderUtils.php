<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 05:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic\Support;

use InvalidArgumentException;

final class OrderUtils
{
    public const string ORDER_ASC  = 'ASC';
    public const string ORDER_DESC = 'DESC';

    /**
     * Normalizes order-by directions to "ASC" / "DESC".
     *
     * @param array<string, string>|null $orderBy
     * @param bool $throwOnInvalid
     * @return array<string, string>
     */
    public static function normalize(?array $orderBy, bool $throwOnInvalid = false): array
    {
        if (empty($orderBy)) {
            return [];
        }

        $normalized = [];

        foreach ($orderBy as $column => $direction) {
            $upper = strtoupper((string) $direction);

            if ($upper !== self::ORDER_ASC && $upper !== self::ORDER_DESC) {
                if ($throwOnInvalid) {
                    throw new InvalidArgumentException(
                        "Invalid order direction: '{$direction}'. Must be 'ASC' or 'DESC'."
                    );
                }

                $upper = self::ORDER_ASC;
            }

            // Column name sanitized (SQL safe)
            $cleanColumn = preg_replace('/[^a-zA-Z0-9_.]/', '', (string) $column);

            if ($cleanColumn !== '' && $cleanColumn !== null) {
                $normalized[$cleanColumn] = $upper;
            }
        }

        return $normalized;
    }

    /**
     * Builds a SQL ORDER BY clause.
     *
     * @param array<string, string>|null $orderBy
     * @param string $quoteChar
     * @return string
     */
    public static function buildSqlOrderBy(?array $orderBy, string $quoteChar = '`'): string
    {
        $normalized = self::normalize($orderBy);

        if (empty($normalized)) {
            return '';
        }

        $parts = [];

        foreach ($normalized as $column => $direction) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_$.]*([.][a-zA-Z_][a-zA-Z0-9_$.]*)*$/', $column)) {
                continue;
            }

            // Quote each part if table.column syntax
            $quoted = implode(
                '.',
                array_map(
                    static fn ($p) => "{$quoteChar}{$p}{$quoteChar}",
                    explode('.', $column)
                )
            );

            $parts[] = "{$quoted} {$direction}";
        }

        return empty($parts)
            ? ''
            : 'ORDER BY ' . implode(', ', $parts);
    }

    /**
     * JSON column ORDER BY for MySQL.
     *
     * @param string $jsonColumn
     * @param string $jsonPath
     * @param string $direction
     * @param string $quoteChar
     * @return string
     */
    public static function buildJsonOrderBy(
        string $jsonColumn,
        string $jsonPath,
        string $direction,
        string $quoteChar = '`'
    ): string {
        $normalized = self::normalize([$jsonColumn => $direction]);
        $safeDir = $normalized[$jsonColumn] ?? self::ORDER_ASC;

        $cleanColumn = preg_replace('/[^a-zA-Z0-9_.]/', '', $jsonColumn);
        if ($cleanColumn === '' || $cleanColumn === null) {
            return '';
        }

        $quoted = implode(
            '.',
            array_map(
                static fn ($p) => "{$quoteChar}{$p}{$quoteChar}",
                explode('.', $cleanColumn)
            )
        );

        $safeJsonPath = addslashes($jsonPath);

        return "JSON_UNQUOTE(JSON_EXTRACT({$quoted}, '{$safeJsonPath}')) {$safeDir}";
    }

    /**
     * Builds a MongoDB sort array.
     *
     * @param array<string, string>|null $orderBy
     * @return array<string, int>
     */
    public static function buildMongoSort(?array $orderBy): array
    {
        $normalized = self::normalize($orderBy);

        if (empty($normalized)) {
            return [];
        }

        $sort = [];

        foreach ($normalized as $column => $direction) {
            $sort[$column] = $direction === self::ORDER_ASC ? 1 : -1;
        }

        return $sort;
    }

    /**
     * Sorts an array of associative rows in memory.
     *
     * @param array<int, array<string, mixed>> $data
     * @param array<string, string>|null $orderBy
     * @return array<int, array<string, mixed>>
     */
    public static function sortArray(array $data, ?array $orderBy): array
    {
        $normalized = self::normalize($orderBy);

        if (empty($normalized)) {
            return $data;
        }

        usort($data, function (mixed $a, mixed $b) use ($normalized) {
            // PHPStan knows $a and $b are array<string, mixed> from param definition
            // Cast to array to satisfy mixed type if needed, but here we can just assert or cast.
            $arrayA = (array)$a;
            $arrayB = (array)$b;

            foreach ($normalized as $column => $dir) {
                /** @var mixed $va */
                $va = $arrayA[$column] ?? null;
                /** @var mixed $vb */
                $vb = $arrayB[$column] ?? null;

                $cmp = OrderUtils::compareValues($va, $vb);

                if ($cmp !== 0) {
                    return $dir === self::ORDER_DESC ? -$cmp : $cmp;
                }
            }

            return 0;
        });

        return $data;
    }

    public static function isValidDirection(string $direction): bool
    {
        return in_array(strtoupper($direction), [self::ORDER_ASC, self::ORDER_DESC], true);
    }

    /**
     * Parses order-from-string: "name:ASC,age:DESC".
     *
     * @param string $orderString
     * @param non-empty-string $pairSeparator
     * @param non-empty-string $keyValueSeparator
     * @return array<string, string>
     */
    public static function fromString(
        string $orderString,
        string $pairSeparator = ',',
        string $keyValueSeparator = ':'
    ): array {
        $orderBy = [];

        if (trim($orderString) === '') {
            return [];
        }

        $pairs = explode($pairSeparator, $orderString);

        foreach ($pairs as $pair) {
            $parts = explode($keyValueSeparator, $pair, 2);

            if (count($parts) === 2) {
                $column = trim($parts[0]);
                $dir = trim($parts[1]);
            } else {
                $column = trim($parts[0]);
                $dir = self::ORDER_ASC;
            }

            $column = preg_replace('/[^a-zA-Z0-9_.]/', '', $column);

            if ($column !== '' && $column !== null) {
                $orderBy[$column] = $dir;
            }
        }

        return self::normalize($orderBy);
    }

    /**
     * Value comparator with null / type awareness.
     *
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    public static function compareValues(mixed $a, mixed $b): int
    {
        if ($a === null && $b === null) {
            return 0;
        }

        if ($a === null) {
            return -1;
        }

        if ($b === null) {
            return 1;
        }

        if (is_numeric($a) && is_numeric($b)) {
            return $a <=> $b;
        }

        if (is_bool($a) && is_bool($b)) {
            return ($a ? 1 : 0) <=> ($b ? 1 : 0);
        }

        if ($a instanceof \DateTimeInterface && $b instanceof \DateTimeInterface) {
            return $a <=> $b;
        }

        if (! is_string($a) && ! is_numeric($a)) {
            // Cannot reliably cast object/array to string for comparison without more context
            return 0;
        }

        if (! is_string($b) && ! is_numeric($b)) {
            return 0;
        }

        return strcmp((string) $a, (string) $b);
    }

    /**
     * Reverse all directions.
     *
     * @param array<string, string> $orderBy
     * @return array<string, string>
     */
    public static function reverse(array $orderBy): array
    {
        $normalized = self::normalize($orderBy);

        foreach ($normalized as &$dir) {
            $dir = $dir === self::ORDER_ASC ? self::ORDER_DESC : self::ORDER_ASC;
        }

        return $normalized;
    }

    /**
     * Merge multiple ORDER BY arrays (later override earlier).
     *
     * @param array<string, string> ...$orderBys
     * @return array<string, string>
     */
    public static function merge(array ...$orderBys): array
    {
        $merged = [];

        foreach ($orderBys as $o) {
            $merged = array_merge($merged, self::normalize($o));
        }

        return $merged;
    }
}
