<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Pagination;

use Maatify\Common\Pagination\DTO\PaginationDTO;

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
class PaginationContext
{
    private ?PaginationEntry $entry = null;

    private ?PaginationDTO $meta = null;

    public function setEntry(PaginationEntry $entry): void
    {
        $this->entry = $entry;
    }

    public function getEntry(): ?PaginationEntry
    {
        return $this->entry;
    }

    public function setMeta(PaginationDTO $meta): void
    {
        $this->meta = $meta;
    }

    public function getMeta(): ?PaginationDTO
    {
        return $this->meta;
    }

    public function hasEntry(): bool
    {
        return $this->entry !== null;
    }

    public function hasMeta(): bool
    {
        return $this->meta !== null;
    }
}
