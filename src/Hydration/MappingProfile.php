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

    private string $currentSource = '';

    /**
     * Start configuring a source key.
     *
     * @param string $sourceKey
     * @return self
     */
    public function forSource(string $sourceKey): self
    {
        $this->currentSource = $sourceKey;
        return $this;
    }

    /**
     * Map current source to destination property.
     *
     * @param string $destinationProperty
     * @return self
     */
    public function mapTo(string $destinationProperty): self
    {
        if ($this->currentSource) {
            $this->mapping[$this->currentSource] = $destinationProperty;
        }
        return $this;
    }

    /**
     * Apply transformer to current source.
     *
     * @param TransformerInterface $transformer
     * @return self
     */
    public function transformWith(TransformerInterface $transformer): self
    {
        if ($this->currentSource) {
            $this->transformers[$this->currentSource] = $transformer;
        }
        return $this;
    }

    /**
     * Set default value. Note: defaults key by destination property usually, but if called in chain,
     * we might infer it from mapping? Or if source is missing?
     * The test implies `forSource('role')->withDefault('guest')`
     * This implies if source 'role' is missing, set DESTINATION property to 'guest'.
     * But we need to know the destination property.
     * If mapTo wasn't called, maybe source=dest?
     * Let's assume standard behavior: defaults array keys are DESTINATION properties.
     *
     * @param mixed $value
     * @return self
     */
    public function withDefault(mixed $value): self
    {
        // If mapTo was called, we know dest. If not, assume dest = source.
        $dest = $this->mapping[$this->currentSource] ?? $this->currentSource;
        $this->defaults[$dest] = $value;
        return $this;
    }

    /**
     * Legacy/Direct methods kept for compatibility if needed, but test uses fluent interface.
     */

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
