<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev DataRepository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-05 11:00:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Examples\Phase29;

use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Pagination\HydratedPaginationCollection;
use Maatify\DataRepository\Pagination\PaginationEntry;

/**
 * @template T of object
 * @extends GenericMySQLRepository<T>
 */
abstract class PaginatedRepository extends GenericMySQLRepository
{
    /**
     * @return HydratedPaginationCollection<T>
     * @throws RepositoryException
     */
    public function getActiveUsersPaginated(int $page = 1): HydratedPaginationCollection
    {
        return $this->paginateObjectsBy(
            filters: ['status' => 1],
            page: $page,
            perPage: 20,
            orderBy: ['id' => 'DESC']
        );
    }
}

// Result Usage (Pseudo-code)
/*
$repo = new MyUserRepo(...);
$result = $repo->getActiveUsersPaginated(1);

$users = $result->data; // array<UserDTO>
$meta  = $result->pagination; // PaginationDTO

echo "Page: " . $meta->page;
echo "Total: " . $meta->total;
*/
