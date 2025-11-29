<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 12:00:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Hydration;

interface HydratorInterface
{
    /**
     * Hydrates a single raw data array into a target object.
     *
     * @param array                 $data    Raw data from repository
     * @param HydrationContext|null $context Contextual options for hydration
     *
     * @return object
     */
    public function hydrate(array $data, ?HydrationContext $context = null): object;

    /**
     * Hydrates a collection of raw data arrays.
     *
     * @param array                 $dataset Array of raw data arrays
     * @param HydrationContext|null $context Contextual options for hydration
     *
     * @return array<object>
     */
    public function hydrateAll(array $dataset, ?HydrationContext $context = null): array;
}
