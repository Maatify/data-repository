<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 14:05:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Hydration;

class MappingProfile
{
    /**
     * @var array<string, string>
     */
    private array $mapping = [];

    /**
     * @var array<string, TransformerInterface>
     */
    private array $transformers = [];

    /**
     * @var array<string, mixed>
     */
    private array $defaults = [];

    /**
     * Map a source key to a destination property.
     *
     * @param string $sourceKey
     * @param string $destinationProperty
     * @return self
     */
    public function addMap(string $sourceKey, string $destinationProperty): self
    {
        $this->mapping[$sourceKey] = $destinationProperty;
        return $this;
    }

    /**
     * Add a transformer for a specific source key.
     *
     * @param string $sourceKey
     * @param TransformerInterface $transformer
     * @return self
     */
    public function addTransformer(string $sourceKey, TransformerInterface $transformer): self
    {
        $this->transformers[$sourceKey] = $transformer;
        return $this;
    }

    /**
     * Set a default value for a destination property if source is missing or null.
     *
     * @param string $destinationProperty
     * @param mixed $value
     * @return self
     */
    public function addDefault(string $destinationProperty, mixed $value): self
    {
        $this->defaults[$destinationProperty] = $value;
        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }

    /**
     * @param string $sourceKey
     * @return TransformerInterface|null
     */
    public function getTransformer(string $sourceKey): ?TransformerInterface
    {
        return $this->transformers[$sourceKey] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }
}
