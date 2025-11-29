<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 18:45
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\GenericRedisRepository;

// Mock Spy Driver for Example
class ExampleSpyRedisDriver
{
    private array $store = [];

    public function set(string $key, string $value): void
    {
        $this->store[$key] = $value;
    }

    public function get(string $key): ?string
    {
        echo "Fetching from Redis: $key\n";
        return $this->store[$key] ?? null;
    }

    public function keys(string $pattern): array
    {
        echo "Scanning keys for pattern: $pattern\n";
        $prefix = str_replace('*', '', $pattern);
        $matches = [];
        foreach (array_keys($this->store) as $k) {
            if (str_starts_with($k, $prefix)) {
                $matches[] = $k;
            }
        }
        return $matches;
    }
}

// Concrete Repository
class UserRedisRepository extends GenericRedisRepository
{
    protected string $keyPrefix = 'user:';

    public function __construct(private object $driver)
    {
        parent::__construct();
    }

    protected function getDriver(): object
    {
        return $this->driver;
    }
}

// 1. Setup Data
$driver = new ExampleSpyRedisDriver();
echo "Seeding 20 users...\n";
for ($i = 1; $i <= 20; $i++) {
    $driver->set("user:$i", json_encode(['id' => $i, 'name' => "User $i"]));
}

// 2. Instantiate Repository
$repo = new UserRedisRepository($driver);

// 3. Paginate (Page 2, 5 items per page)
echo "\n--- Paginating Page 2 (Limit 5, Offset 5) ---\n";
// This should trigger 1 'keys' call and 5 'get' calls (NOT 20 'get' calls)
$result = $repo->paginate(2, 5);

echo "\n--- Results ---\n";
echo "Page: " . $result->pagination->page . "\n";
echo "Total: " . $result->pagination->total . "\n";
echo "Items count: " . count($result->data) . "\n";

foreach ($result->data as $item) {
    echo " - " . $item['name'] . "\n";
}
