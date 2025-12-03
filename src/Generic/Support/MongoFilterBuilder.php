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

final class MongoFilterBuilder
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
    private const array MONGO_RESERVED = [
        '$where', '$group', '$match',
    ];

    /**
     * @param array<array-key,mixed> $filters
     * @return array<string|int, mixed>
     */
    public function build(array $filters): array
    {
        $parser = new FilterParser();
        $conditions = $parser->parse($filters);

        /** @var array<string,mixed> $final */
        $final = [];

        foreach ($conditions as $filter) {
            $field = $filter->field === 'id' ? '_id' : $filter->field;

            if (! $this->isValidMongoField($field)) {
                throw new InvalidArgumentException("Invalid Mongo field '{$field}'");
            }

            /** @var array<string,mixed> $condition */
            $condition = $this->buildMongoCondition($field, $filter->operator, $filter->value);

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
                $final[$field] = $condition[$field];
            }
        }

        return $final;
    }

    /**
     * @return array<string,mixed>
     */
    private function buildMongoCondition(string $field, string $op, mixed $value): array
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

        return [$field => []];
    }

    private function isValidMongoField(string $name): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name) === 1
            && ! in_array(strtolower($name), self::MONGO_RESERVED, true);
    }
}
