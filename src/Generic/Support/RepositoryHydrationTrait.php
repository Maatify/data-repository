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
use Maatify\DataRepository\Hydration\HydratorInterface;
use Maatify\DataRepository\Pagination\HydratedPaginationCollection;

/**
 * @template T of object
 */
trait RepositoryHydrationTrait
{
    /**
     * @param int|string $id
     * @return T|null
     * @throws RepositoryException
     */
    public function findObject(int|string $id): ?object
    {
        $data = $this->find($id);
        if ($data === null) {
            return null;
        }

        if ($this->hydrator) {
            /** @var array<string, mixed> $data */
            return $this->hydrator->hydrate($data);
        }

        /** @var T $obj */
        $obj = (object)$data; // @phpstan-ignore-line fallback to stdClass if no hydrator
        return $obj;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<T>
     * @throws RepositoryException
     */
    public function findObjectsBy(array $filters): array
    {
        $data = $this->findBy($filters);

        if ($this->hydrator) {
            /** @var array<int, array<string, mixed>> $data */
            return $this->hydrator->hydrateAll($data);
        }

        return array_map(function ($row) {
            /** @var T $obj */
            $obj = (object)$row; // @phpstan-ignore-line fallback
            return $obj;
        }, $data);
    }

    /**
     * @param int $page
     * @param int $perPage
     * @param array<string, string>|null $orderBy
     * @return HydratedPaginationCollection<T>
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
     * @return HydratedPaginationCollection<T>
     * @throws RepositoryException
     */
    public function paginateObjectsBy(array $filters, int $page = 1, int $perPage = 10, ?array $orderBy = null): HydratedPaginationCollection
    {
        // Assuming $this->paginateBy is available via the class using this trait
        /** @var PaginationResultDTO $result */
        $result = $this->paginateBy($filters, $page, $perPage, $orderBy);

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $result->data;

        if ($this->hydrator) {
            $objects = $this->hydrator->hydrateAll($rows);
        } else {
            $objects = array_map(function ($item) {
                /** @var T $obj */
                $obj = (object)$item; // @phpstan-ignore-line fallback
                return $obj;
            }, $rows);
        }

        return new HydratedPaginationCollection($objects, $result->pagination);
    }
}
