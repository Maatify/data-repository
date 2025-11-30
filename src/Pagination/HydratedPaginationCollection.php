<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 19:00:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Pagination;

use Maatify\Common\Pagination\DTO\PaginationDTO;

class HydratedPaginationCollection
{
    /**
     * @param array<object> $data
     * @param PaginationDTO $pagination
     */
    public function __construct(
        public array $data,
        public PaginationDTO $pagination
    ) {
    }
}
