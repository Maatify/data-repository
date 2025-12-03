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

final class MySQLOrderBuilder
{
    /**
     * Builds a SQL ORDER BY clause.
     *
     * @param array<string, string>|null $orderBy
     * @param string $quoteChar
     * @return string
     */
    public function build(?array $orderBy, string $quoteChar = '`'): string
    {
        $normalized = OrderUtils::normalize($orderBy);

        if (empty($normalized)) {
            return '';
        }

        $parts = [];

        foreach ($normalized as $column => $direction) {
            // Validate column format to prevent SQL injection
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
    public function buildJson(
        string $jsonColumn,
        string $jsonPath,
        string $direction,
        string $quoteChar = '`'
    ): string {
        $normalized = OrderUtils::normalize([$jsonColumn => $direction]);
        $safeDir = $normalized[$jsonColumn] ?? OrderUtils::ORDER_ASC;

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
        if ($safeJsonPath === '') {
            return '';
        }

        // Ensure the JSON path begins with '$'
        if ($safeJsonPath[0] !== '$') {
            // Example cases:
            // user.level     → $.user.level
            // .user.level    → $.user.level
            // $.user.level   → (valid)
            $safeJsonPath = ltrim($safeJsonPath, '.');
            $safeJsonPath = '$.' . $safeJsonPath;
        }

        return "JSON_UNQUOTE(JSON_EXTRACT({$quoted}, '{$safeJsonPath}')) {$safeDir}";
    }
}
