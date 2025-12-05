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

namespace Maatify\DataRepository\Generic\Support;

class NormalizerOptions
{
    public function __construct(
        private bool $keepMongoId = false,
        private bool $recursive = false,
        private bool $strictIdTypes = true
    ) {
    }

    public static function create(): self
    {
        return new self();
    }

    public function withKeepMongoId(bool $keepMongoId): self
    {
        $clone = clone $this;
        $clone->keepMongoId = $keepMongoId;
        return $clone;
    }

    public function withRecursive(bool $recursive): self
    {
        $clone = clone $this;
        $clone->recursive = $recursive;
        return $clone;
    }

    public function withStrictIdTypes(bool $strictIdTypes): self
    {
        $clone = clone $this;
        $clone->strictIdTypes = $strictIdTypes;
        return $clone;
    }

    public function shouldKeepMongoId(): bool
    {
        return $this->keepMongoId;
    }

    public function isRecursive(): bool
    {
        return $this->recursive;
    }

    public function isStrictIdTypes(): bool
    {
        return $this->strictIdTypes;
    }
}
