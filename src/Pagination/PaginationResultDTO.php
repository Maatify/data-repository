<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 16:30
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Pagination;

use Maatify\Common\Pagination\PaginationDTO;

class PaginationResultDTO
{
    /**
     * @param   array<int, mixed>  $data
     * @param   PaginationDTO      $pagination
     */
    public function __construct(
        public array $data,
        public PaginationDTO $pagination
    ) {
    }
}
