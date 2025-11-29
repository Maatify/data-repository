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

class HydrationContext
{
    public const STAGE_PREPARE = 'prepare';
    public const STAGE_CAST = 'cast';
    public const STAGE_MAP = 'map';
    public const STAGE_VALIDATE = 'validate';
    public const STAGE_COMPLETE = 'complete';

    /**
     * @var array<string>
     */
    private array $stages = [];

    /**
     * @var array<string, mixed>
     */
    private array $meta = [];

    private ?MappingProfile $profile = null;

    public function __construct()
    {
        $this->stages = [
            self::STAGE_PREPARE,
            self::STAGE_CAST,
            self::STAGE_MAP,
            self::STAGE_VALIDATE,
            self::STAGE_COMPLETE,
        ];
    }

    /**
     * @return array<string>
     */
    public function getStages(): array
    {
        return $this->stages;
    }

    /**
     * @param array<string> $stages
     * @return $this
     */
    public function setStages(array $stages): self
    {
        $this->stages = $stages;
        return $this;
    }

    public function addMeta(string $key, mixed $value): self
    {
        $this->meta[$key] = $value;
        return $this;
    }

    public function getMeta(string $key): mixed
    {
        return $this->meta[$key] ?? null;
    }

    public function setProfile(MappingProfile $profile): self
    {
        $this->profile = $profile;
        return $this;
    }

    public function getProfile(): ?MappingProfile
    {
        return $this->profile;
    }
}
