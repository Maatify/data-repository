<?php

/**
 * @copyright   ©2024 Maatify.dev
 * @Library     Maatify.dev DataRepository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2024-12-02 11:00:00
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

    protected function onMap(object $instance, ?HydrationContext $context = null): object
    {
        // Custom mapping if property names don't match source keys
        $profile = new MappingProfile();
        $profile->forSource('is_active')->mapTo('isActive');
        $profile->forSource('joined_at')->mapTo('joinedAt');

        // BaseHydrator handles auto-mapping for matching names
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

$hydrator = new UserHydrator();
$userDto = $hydrator->hydrate($data);

var_dump($userDto);
