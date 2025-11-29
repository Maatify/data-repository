<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-29 02:07
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic\Support;

use InvalidArgumentException;
use MongoDB\BSON\ObjectId;

/**
 * @phpstan-type AssocRow array<string, mixed>
 * @phpstan-type AssocRowList array<int, AssocRow>
 */
final class ResultNormalizer
{
    public function __construct(
        private bool $keepMongoId = false,
        private bool $recursive = false,
        private bool $strictIdTypes = true
    ) {
    }

    // ───────────────────────────────────────────────
    //  STATIC API — DEFAULT BEHAVIOR
    // ───────────────────────────────────────────────

    /**
     * @param AssocRow|null $row
     * @return AssocRow|null
     */
    public static function normalize(?array $row): ?array
    {
        return (new self())->normalizeRow($row);
    }

    /**
     * @param AssocRow|null $row
     * @return AssocRow|null
     */
    public static function normalizeWithConfig(
        ?array $row,
        bool $keepMongoId = false,
        bool $recursive = false,
        bool $strictIdTypes = true
    ): ?array {
        return (new self($keepMongoId, $recursive, $strictIdTypes))->normalizeRow($row);
    }

    /**
     * @param AssocRowList $rows
     * @return AssocRowList
     */
    public static function normalizeAll(array $rows): array
    {
        return (new self())->normalizeRows($rows);
    }

    /**
     * @param AssocRowList $rows
     * @return AssocRowList
     */
    public static function normalizeAllWithConfig(
        array $rows,
        bool $keepMongoId = false,
        bool $recursive = false,
        bool $strictIdTypes = true
    ): array {
        return (new self($keepMongoId, $recursive, $strictIdTypes))->normalizeRows($rows);
    }

    // ───────────────────────────────────────────────
    //  INSTANCE API
    // ───────────────────────────────────────────────

    /**
     * @param AssocRow|null $row
     * @return AssocRow|null
     */
    public function normalizeRow(?array $row): ?array
    {
        if ($row === null) {
            return null;
        }

        // Map _id ⇒ id (only if id not already provided)
        if (array_key_exists('_id', $row) && !array_key_exists('id', $row)) {
            $row['id'] = $row['_id'];
        }

        // Normalize values
        foreach ($row as $key => $value) {
            $row[$key] = $this->normalizeValue($value);
        }

        // Remove duplicated _id
        if (
            !$this->keepMongoId &&
            isset($row['_id'], $row['id']) &&
            $row['id'] === $row['_id']
        ) {
            unset($row['_id']);
        }

        return $row;
    }

    /**
     * @param AssocRowList $rows
     * @return AssocRowList
     */
    public function normalizeRows(array $rows): array
    {
        $output = [];
        foreach ($rows as $i => $row) {
            $normalized = $this->normalizeRow($row);
            if ($normalized !== null) {
                $output[$i] = $normalized;
            }
        }
        return $output;
    }

    // ───────────────────────────────────────────────
    //  VALUE NORMALIZATION
    // ───────────────────────────────────────────────

    /**
     * @param   mixed  $value
     *
     * @return mixed
     */
    private function normalizeValue(mixed $value): mixed
    {
        // Recursive array normalization
        if ($this->recursive && is_array($value)) {
            if (array_is_list($value)) {
                return $this->normalizeRowsArray($value);
            }

            // Assert a row type before using normalizeRowArray()
            $this->assertAssocRow($value);

            // Now safe to treat as associative row
            return $this->normalizeRowArray($value);
        }

        // Convert ObjectId to string
        if ($value instanceof ObjectId) {
            return (string)$value;
        }

        // Convert stringable objects
        if (is_object($value) && method_exists($value, '__toString')) {
            return (string)$value;
        }

        // Strict ID validation
        if ($this->strictIdTypes && $this->isIdValue($value)) {
            return $this->normalizeStrictId($value);
        }

        return $value;
    }

    /**
     * @param array<mixed, mixed> $array
     * @phpstan-assert array<string, mixed> $array
     */
    private function assertAssocRow(array $array): void
    {
        foreach ($array as $key => $_) {
            if (!is_string($key)) {
                throw new \InvalidArgumentException(
                    'normalizeRowArray() can only be called on associative (string-keyed) arrays.'
                );
            }
        }
    }

    /**
     * @param array<mixed> $array
     * @return array<mixed>
     */
    private function normalizeRowsArray(array $array): array
    {
        $out = [];
        foreach ($array as $k => $v) {
            $out[$k] = $this->normalizeValue($v);
        }
        return $out;
    }

    /**
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    private function normalizeRowArray(array $array): array
    {
        return $this->normalizeRow($array) ?? [];
    }

    // ───────────────────────────────────────────────
    //  ID HANDLING
    // ───────────────────────────────────────────────

    private function isIdValue(mixed $value): bool
    {
        return $value instanceof ObjectId
               || is_int($value)
               || (is_string($value) && $this->looksLikeIdString($value));
    }

    private function looksLikeIdString(string $value): bool
    {
        return preg_match('/^[a-f0-9]{24}$/i', $value) === 1 || is_numeric($value);
    }

    /**
     * @param   mixed  $id
     *
     * @return string|int
     */
    private function normalizeStrictId(mixed $id): string|int
    {
        if ($id instanceof ObjectId) {
            return (string)$id;
        }

        if (is_int($id)) {
            return $id;
        }

        if (is_string($id)) {
            if (!$this->looksLikeIdString($id)) {
                throw new InvalidArgumentException(
                    sprintf('Invalid ID format: "%s"', substr($id, 0, 50))
                );
            }
            return $id;
        }

        throw new InvalidArgumentException(
            sprintf('Unsupported ID type: %s', gettype($id))
        );
    }

    // ───────────────────────────────────────────────
    //  FLUENT CONFIGURATION
    // ───────────────────────────────────────────────

    public static function create(): self
    {
        return new self();
    }

    public function keepMongoId(bool $keep = true): self
    {
        $this->keepMongoId = $keep;
        return $this;
    }

    public function recursive(bool $recursive = true): self
    {
        $this->recursive = $recursive;
        return $this;
    }

    public function strictIdTypes(bool $strict = true): self
    {
        $this->strictIdTypes = $strict;
        return $this;
    }

    /**
     * @param AssocRowList $rows
     * @return AssocRowList
     */
    public function normalizeBatch(array $rows): array
    {
        return $this->normalizeRows($rows);
    }
}
