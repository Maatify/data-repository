<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-02 08:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic\Pagination;

class LimitOffsetConfig
{
    public function __construct(
        private int $maxLimit = 10000,
        private int $maxOffset = 100000
    ) {
    }

    public static function create(): self
    {
        return new self();
    }

    public function withMaxLimit(int $maxLimit): self
    {
        $clone = clone $this;
        $clone->maxLimit = $maxLimit;
        return $clone;
    }

    public function withMaxOffset(int $maxOffset): self
    {
        $clone = clone $this;
        $clone->maxOffset = $maxOffset;
        return $clone;
    }

    public function getMaxLimit(): int
    {
        return $this->maxLimit;
    }

    public function getMaxOffset(): int
    {
        return $this->maxOffset;
    }
}
