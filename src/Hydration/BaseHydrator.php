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

abstract class BaseHydrator implements HydratorInterface
{
    /**
     * @param array<string, mixed>  $data
     * @param HydrationContext|null $context
     *
     * @return object
     * @throws RepositoryException
     */
    public function hydrate(array $data, ?HydrationContext $context = null): object
    {
        $context ??= new HydrationContext();
        $stages = $context->getStages();

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
                    $this->ensureInstance($instance);
                    // @var object $instance
                    $instance = $this->onMap($data, $instance);
                    break;
                case HydrationContext::STAGE_VALIDATE:
                    $this->ensureInstance($instance);
                    // @var object $instance
                    $this->onValidate($instance);
                    break;
                case HydrationContext::STAGE_COMPLETE:
                    $this->ensureInstance($instance);
                    // @var object $instance
                    $this->onComplete($instance);
                    break;
            }
        }

        $this->ensureInstance($instance);
        // @var object $instance
        return $instance;
    }

    /**
     * @param array<int, array<string, mixed>> $dataset
     * @param HydrationContext|null            $context
     *
     * @return array<object>
     * @throws RepositoryException
     */
    public function hydrateAll(array $dataset, ?HydrationContext $context = null): array
    {
        $result = [];
        foreach ($dataset as $row) {
            if (is_array($row)) {
                $result[] = $this->hydrate($row, $context);
            }
        }
        return $result;
    }

    abstract protected function createInstance(): object;

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
        return $data;
    }

    /**
     * Stage: Map
     * Assign data to the object instance.
     * Default implementation assumes public properties or magic setters,
     * but concrete classes should likely override this for specific logic.
     *
     * @param array<string, mixed> $data
     * @param object               $instance
     *
     * @return object
     */
    protected function onMap(array $data, object $instance): object
    {
        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->{$key} = $value;
            }
        }
        return $instance;
    }

    /**
     * Stage: Validate
     * Perform consistency checks on the hydrated object.
     *
     * @param object $instance
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
     * @param object $instance
     */
    protected function onComplete(object $instance): void
    {
        // Override for final touches
    }

    /**
     * Helper to ensure instance exists before late stages.
     *
     * @param object|null $instance
     *
     * @return void
     */
    private function ensureInstance(?object &$instance): void
    {
        if ($instance === null) {
            $instance = $this->createInstance();
        }
    }
}
