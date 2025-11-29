<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 15:30:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Examples\Phase14;

use DateTimeImmutable;
use Maatify\DataRepository\Hydration\BaseHydrator;
use Maatify\DataRepository\Hydration\HydrationContext;
use Maatify\DataRepository\Hydration\MappingProfile;
use Maatify\DataRepository\Hydration\Transformers\DateTimeTransformer;

// 1. Define the DTO
class UserDto
{
    public int $id;
    public string $name;
    public string $email;
    public DateTimeImmutable $registeredAt;
    public string $status;
}

// 2. Define the Hydrator
class UserHydrator extends BaseHydrator
{
    protected function createInstance(): object
    {
        return new UserDto();
    }
}

// 3. Define the Mapping Profile
$profile = new MappingProfile();
$profile->addMap('user_id', 'id')
    ->addMap('full_name', 'name')
    ->addMap('user_email', 'email')
    ->addMap('created_at', 'registeredAt')
    ->addDefault('status', 'active')
    ->addTransformer('created_at', new DateTimeTransformer('Y-m-d H:i:s'));

// 4. Simulate Data Source
$data = [
    'user_id' => 101,
    'full_name' => 'Alice Wonderland',
    'user_email' => 'alice@example.com',
    'created_at' => '2023-10-15 08:30:00',
];

// 5. Hydrate
$hydrator = new UserHydrator();
$context = new HydrationContext();
$context->setProfile($profile);

/** @var UserDto $user */
$user = $hydrator->hydrate($data, $context);

// 6. Output
echo "User: " . $user->name . "\n";
echo "Registered: " . $user->registeredAt->format(DATE_ATOM) . "\n";
echo "Status: " . $user->status . "\n";
