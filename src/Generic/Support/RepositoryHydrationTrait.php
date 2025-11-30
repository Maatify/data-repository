<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 15:00:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Generic\Support;

use Maatify\Common\Pagination\DTO\PaginationResultDTO;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Pagination\HydratedPaginationCollection;

trait RepositoryHydrationTrait
{
    /**
     * @param int|string $id
     * @return object|null
     * @throws RepositoryException
     */
    public function findObject(int|string $id): ?object
    {
        $data = $this->find($id);
        if ($data === null) {
            return null;
        }

        if ($this->hydrator) {
            return $this->hydrator->hydrate($data);
        }

        // If no hydrator is set, we can either throw exception or return (object)$data.
        // Returning object cast is safer than crashing, but strictly we should probably require a hydrator.
        return (object)$data;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<object>
     * @throws RepositoryException
     */
    public function findObjectsBy(array $filters): array
    {
        $data = $this->findBy($filters);

        if ($this->hydrator) {
            return $this->hydrator->hydrateAll($data);
        }

        return array_map(fn ($row) => (object)$row, $data);
    }

    /**
     * @param int $page
     * @param int $perPage
     * @param array<string, string>|null $orderBy
     * @return HydratedPaginationCollection
     * @throws RepositoryException
     */
    public function paginateObjects(int $page = 1, int $perPage = 10, ?array $orderBy = null): HydratedPaginationCollection
    {
        return $this->paginateObjectsBy([], $page, $perPage, $orderBy);
    }

    /**
     * @param array<string, mixed> $filters
     * @param int $page
     * @param int $perPage
     * @param array<string, string>|null $orderBy
     * @return HydratedPaginationCollection
     * @throws RepositoryException
     */
    public function paginateObjectsBy(array $filters, int $page = 1, int $perPage = 10, ?array $orderBy = null): HydratedPaginationCollection
    {
        // Assuming $this->paginateBy is available via the class using this trait
        /** @var PaginationResultDTO $result */
        $result = $this->paginateBy($filters, $page, $perPage, $orderBy);

        $objects = $this->hydrator ? $this->hydrator->hydrateAll($result->data) : array_map(fn ($item) => (object)$item, $result->data);

        return new HydratedPaginationCollection($objects, $result->pagination);
    }
}
