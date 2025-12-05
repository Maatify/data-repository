<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 12:10:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Hydration;

use Maatify\DataRepository\Exceptions\RepositoryException;

/**
 * @template T of object
 * @implements HydratorInterface<T>
 */
abstract class BaseHydrator implements HydratorInterface
{
    /**
     * @param array<string, mixed>  $data
     * @param HydrationContext|null $context
     *
     * @return T
     * @throws RepositoryException
     */
    public function hydrate(array $data, ?HydrationContext $context = null): object
    {
        $context ??= new HydrationContext();
        $stages = $context->getStages();

        /** @var T|null $instance */
        $instance = null;

        foreach ($stages as $stage) {
            switch ($stage) {
                case HydrationContext::STAGE_PREPARE:
                    $data = $this->onPrepare($data);
                    break;
                case HydrationContext::STAGE_CAST:
                    $data = $this->onCast($data);
                    break;
                case HydrationContext::STAGE_MAP:
                    $instance = $this->ensureInstance($instance);
                    $instance = $this->onMap($data, $instance, $context);
                    break;
                case HydrationContext::STAGE_VALIDATE:
                    $instance = $this->ensureInstance($instance);
                    $this->onValidate($instance);
                    break;
                case HydrationContext::STAGE_COMPLETE:
                    $instance = $this->ensureInstance($instance);
                    $this->onComplete($instance);
                    break;
            }
        }

        return $this->ensureInstance($instance);
    }

    /**
     * @param array<int, array<string, mixed>> $dataset
     * @param HydrationContext|null            $context
     *
     * @return array<T>
     * @throws RepositoryException
     */
    public function hydrateAll(array $dataset, ?HydrationContext $context = null): array
    {
        $result = [];
        foreach ($dataset as $row) {
            $result[] = $this->hydrate($row, $context);
        }
        return $result;
    }

    /**
     * @return T
     */
    abstract protected function createInstance(): object;

    /**
     * Define type casting rules for fields.
     * Override this method to return an associative array of field => type.
     * Supported types: 'int', 'float', 'bool', 'string', 'datetime', 'json'.
     *
     * @return array<string, string>
     */
    protected function getCastingDefinitions(): array
    {
        return [];
    }

    /**
     * Stage: Prepare
     * Clean or normalize raw data keys/values before casting.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function onPrepare(array $data): array
    {
        return $data;
    }

    /**
     * Stage: Cast
     * Convert primitive types (strings to ints, dates, etc.).
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function onCast(array $data): array
    {
        $definitions = $this->getCastingDefinitions();
        if (!empty($definitions)) {
            return AutoCaster::cast($data, $definitions);
        }
        return $data;
    }

    /**
     * Stage: Map
     * Assign data to the object instance.
     * Default implementation assumes public properties or magic setters,
     * but concrete classes should likely override this for specific logic.
     *
     * @param array<string, mixed> $data
     * @param T                    $instance
     * @param HydrationContext|null $context
     *
     * @return T
     */
    protected function onMap(array $data, object $instance, ?HydrationContext $context = null): object
    {
        $profile = $context?->getProfile();

        if ($profile) {
            $mapping = $profile->getMapping();
            $defaults = $profile->getDefaults();

            // Apply Defaults
            foreach ($defaults as $destProp => $defaultVal) {
                if (property_exists($instance, $destProp)) {
                    $instance->{$destProp} = $defaultVal;
                }
            }

            // Apply Mappings
            foreach ($data as $sourceKey => $value) {
                $destProp = $mapping[$sourceKey] ?? $sourceKey;
                $transformer = $profile->getTransformer($sourceKey);

                if ($transformer) {
                    $value = $transformer->transform($value);
                }

                if (property_exists($instance, $destProp)) {
                    $instance->{$destProp} = $value;
                }
            }
        } else {
            // Default behavior
            foreach ($data as $key => $value) {
                if (property_exists($instance, $key)) {
                    $instance->{$key} = $value;
                }
            }
        }

        return $instance;
    }

    /**
     * Stage: Validate
     * Perform consistency checks on the hydrated object.
     *
     * @param T $instance
     *
     * @throws RepositoryException
     */
    protected function onValidate(object $instance): void
    {
        // Override to implement validation
    }

    /**
     * Stage: Complete
     * Final hook after object is ready.
     *
     * @param T $instance
     */
    protected function onComplete(object $instance): void
    {
        // Override for final touches
    }

    /**
     * Helper to ensure instance exists before late stages.
     *
     * @param T|null $instance
     *
     * @return T
     */
    private function ensureInstance(?object $instance): object
    {
        if ($instance === null) {
            $instance = $this->createInstance();
        }
        return $instance;
    }
}
