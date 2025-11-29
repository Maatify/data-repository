<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Pagination;

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify DataRepository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 11:00:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */
class PaginationEntry
{
    private int $page;

    private int $perPage;

    public function __construct(int $page = 1, int $perPage = 20)
    {
        $this->page = max(1, $page);
        $this->perPage = max(1, $perPage);
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}
