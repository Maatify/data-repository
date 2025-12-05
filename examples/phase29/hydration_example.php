<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev DataRepository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-05 11:00:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Examples\Phase29;

use Maatify\DataRepository\Hydration\BaseHydrator;
use Maatify\DataRepository\Hydration\HydrationContext;
use Maatify\DataRepository\Hydration\MappingProfile;

class UserDTO
{
    public int $id;
    public string $name;
    public string $email;
    public bool $isActive;
    public \DateTimeImmutable $joinedAt;
}

/**
 * @extends BaseHydrator<UserDTO>
 */
class UserHydrator extends BaseHydrator
{
    protected function createInstance(): object
    {
        return new UserDTO();
    }

    protected function getCastingDefinitions(): array
    {
        return [
            'id' => 'int',
            'is_active' => 'bool',
            'joined_at' => 'datetime',
        ];
    }

    /**
     * Override onMap to add custom logic AFTER standard mapping.
     */
    protected function onMap(array $data, object $instance, ?HydrationContext $context = null): object
    {
        // 1. Run standard mapping (handles defaults + MappingProfile if present)
        parent::onMap($data, $instance, $context);

        // 2. Custom logic: Example of a calculated/modified field
        if (!empty($instance->name)) {
            $instance->name = strtoupper($instance->name);
        }

        return $instance;
    }
}

// Usage
$data = [
    'id' => '123', // Will be cast to int
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'is_active' => '1', // Will be cast to bool
    'joined_at' => '2024-01-01 12:00:00', // Will be cast to DateTimeImmutable
];

// Define Mapping Profile for key mismatches
$profile = new MappingProfile();
$profile->forSource('is_active')->mapTo('isActive');
$profile->forSource('joined_at')->mapTo('joinedAt');

// Create Context with Profile
$context = new HydrationContext();
$context->setProfile($profile);

$hydrator = new UserHydrator();
// Pass context to hydrate
$userDto = $hydrator->hydrate($data, $context);

var_dump($userDto);
