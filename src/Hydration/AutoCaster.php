<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 13:00:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Hydration;

use DateTimeImmutable;
use Exception;

class AutoCaster
{
    public const TYPE_INT = 'int';
    public const TYPE_FLOAT = 'float';
    public const TYPE_BOOL = 'bool';
    public const TYPE_STRING = 'string';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_JSON = 'json';

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $definitions
     *
     * @return array<string, mixed>
     */
    public static function cast(array $data, array $definitions): array
    {
        foreach ($definitions as $field => $type) {
            if (array_key_exists($field, $data)) {
                $data[$field] = self::castValue($data[$field], $type);
            }
        }
        return $data;
    }

    /**
     * @param mixed $value
     * @param string $type
     *
     * @return mixed
     */
    private static function castValue(mixed $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            self::TYPE_INT => (int)$value,
            self::TYPE_FLOAT => (float)$value,
            self::TYPE_BOOL => (bool)$value,
            self::TYPE_STRING => (string)$value,
            self::TYPE_JSON => self::castJson($value),
            self::TYPE_DATETIME => self::castDateTime($value),
            default => $value,
        };
    }

    private static function castJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private static function castDateTime(mixed $value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if (is_string($value)) {
            try {
                return new DateTimeImmutable($value);
            } catch (Exception) {
                // Return null or throw exception?
                // Given strict mode usually prefers correctness, but auto-casting implies "best effort" or "standardized".
                // If it fails, maybe return null.
                return null;
            }
        }

        if (is_int($value)) {
            // Timestamp
             return (new DateTimeImmutable())->setTimestamp($value);
        }

        return null;
    }
}
