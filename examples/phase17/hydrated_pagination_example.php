<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 19:10:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Example;

use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Hydration\BaseHydrator;
use Maatify\DataRepository\Pagination\HydratedPaginationCollection;

// 1. Define Entity
class UserEntity
{
    public int $id;
    public string $name;
}

// 2. Define Hydrator
class UserHydrator extends BaseHydrator
{
    protected function createInstance(): object
    {
        return new UserEntity();
    }
}

// 3. Define Repository (Simulated for example)
class UserRepository extends GenericMySQLRepository
{
    // In a real app, connection logic happens here
}

// 4. Usage
// $repo = new UserRepository($adapter);
// $repo->setHydrator(new UserHydrator());
//
// /** @var HydratedPaginationCollection $result */
// $result = $repo->paginateObjects(1, 20);
//
// foreach ($result->data as $user) {
//     echo $user->name; // Typed property access
// }
//
// echo "Total Users: " . $result->pagination->total;

echo "Hydrated pagination example code (simulated) loaded successfully.\n";
