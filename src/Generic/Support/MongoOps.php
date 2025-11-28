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

use MongoDB\Collection;

/**
 * 🔌 MongoOps
 *
 * Thin normalization wrapper around MongoDB Collection (or compatible fake).
 *
 * It intentionally stays minimal for now and can be extended with helpers
 * as the library evolves.
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
}
