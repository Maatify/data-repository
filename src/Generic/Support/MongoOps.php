<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 02:45
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic\Support;

use MongoDB\BSON\ObjectId;
use MongoDB\Collection;

/**
 * 🔌 MongoOps
 *
 * Thin normalization wrapper around MongoDB Collection (or compatible fake).
 * Centralizes BSON conversion and cursor iteration.
 */
final class MongoOps
{
    /**
     * @var Collection|object
     */
    private object $collection;

    /**
     * @param Collection|object $collection MongoDB Collection or compatible fake
     */
    public function __construct(object $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Expose a raw collection for advanced usages.
     *
     * @return Collection|object
     */
    public function getCollection(): object
    {
        return $this->collection;
    }

    /**
     * Normalize ID from InsertOneResult or similar.
     *
     * @param mixed $id
     * @return int|string
     */
    public function normalizeInsertedId(mixed $id): int|string
    {
        if ($id instanceof ObjectId) {
            return (string)$id;
        }
        if (is_int($id) || is_string($id)) {
            return $id;
        }

        // Fallback for custom objects that stringify
        if (is_object($id) && method_exists($id, '__toString')) {
            return (string)$id;
        }

        return '';
    }

    /**
     * Convert MongoDB BSONDocument/array/object to standard PHP array.
     *
     * @param mixed $document
     * @return array<string, mixed>|null
     */
    public function toArray(mixed $document): ?array
    {
        if ($document === null) {
            return null;
        }

        if (is_object($document) && method_exists($document, 'getArrayCopy')) {
            /** @var array<string, mixed> $array */
            $array = $document->getArrayCopy();
            return $array;
        }

        /** @var array<string, mixed> $array */
        $array = (array)$document;
        return $array;
    }

    /**
     * Convert Cursor to Array of Arrays.
     *
     * @param iterable<mixed> $cursor
     * @return array<int, array<string, mixed>>
     */
    public function cursorToArray(iterable $cursor): array
    {
        $results = [];
        foreach ($cursor as $document) {
            /** @var array<string, mixed>|null $arr */
            $arr = $this->toArray($document);
            if ($arr !== null) {
                $results[] = $arr;
            }
        }
        return $results;
    }
}
