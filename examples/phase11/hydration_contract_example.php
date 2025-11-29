<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 12:15:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Examples\Phase11;

require_once __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Hydration\HydrationContext;
use Maatify\DataRepository\Hydration\HydratorInterface;

// 1. Define a simple DTO
class UserDTO
{
    public int $id;
    public string $name;
    public string $email;
    public ?string $locale;

    public function __construct(int $id, string $name, string $email, ?string $locale = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->locale = $locale;
    }
}

// 2. Implement the HydratorInterface
class UserHydrator implements HydratorInterface
{
    public function hydrate(array $data, ?HydrationContext $context = null): object
    {
        // Extract context metadata if available
        $locale = $context?->getMeta('locale') ?? $data['locale'] ?? 'en_US';

        return new UserDTO(
            id: (int)$data['id'],
            name: (string)$data['name'],
            email: (string)$data['email'],
            locale: (string)$locale
        );
    }

    public function hydrateAll(array $dataset, ?HydrationContext $context = null): array
    {
        $results = [];
        foreach ($dataset as $data) {
            $results[] = $this->hydrate($data, $context);
        }
        return $results;
    }
}

// 3. Usage Example
echo "=== Phase 11: Hydrator Interface Example ===\n\n";

$dataset = [
    ['id' => 101, 'name' => 'Alice', 'email' => 'alice@example.com'],
    ['id' => 102, 'name' => 'Bob', 'email' => 'bob@example.com', 'locale' => 'fr_FR'],
];

// Initialize Context
$context = new HydrationContext();
$context->addMeta('locale', 'de_DE'); // Default override

$hydrator = new UserHydrator();

// Single Hydration
echo "--> Single Hydration:\n";
$user = $hydrator->hydrate($dataset[0], $context);
print_r($user);

// Bulk Hydration
echo "\n--> Bulk Hydration:\n";
$users = $hydrator->hydrateAll($dataset, $context);
print_r($users);

echo "\nDone.\n";
