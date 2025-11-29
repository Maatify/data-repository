<?php

require_once __DIR__ . '/../../vendor/autoload.php'; // adjusting path for example

use Maatify\DataRepository\Hydration\BaseHydrator;
use Maatify\DataRepository\Hydration\AutoCaster;

class UserHydrator extends BaseHydrator
{
    protected function createInstance(): object
    {
        return new class {
            public int $id;
            public string $name;
            public bool $is_active;
            public array $settings;
            public \DateTimeImmutable $created_at;
        };
    }

    protected function getCastingDefinitions(): array
    {
        return [
            'id' => AutoCaster::TYPE_INT,
            'is_active' => AutoCaster::TYPE_BOOL,
            'settings' => AutoCaster::TYPE_JSON,
            'created_at' => AutoCaster::TYPE_DATETIME,
        ];
    }
}

// Simulated raw data from DB (all strings)
$data = [
    'id' => '42',
    'name' => 'Alice',
    'is_active' => '1',
    'settings' => '{"theme":"dark","notifications":true}',
    'created_at' => '2023-11-27 10:00:00',
];

$hydrator = new UserHydrator();
$user = $hydrator->hydrate($data);

echo "User ID: " . $user->id . " (Type: " . gettype($user->id) . ")\n";
echo "Active: " . ($user->is_active ? 'Yes' : 'No') . " (Type: " . gettype($user->is_active) . ")\n";
echo "Settings: " . print_r($user->settings, true) . "\n";
echo "Created: " . $user->created_at->format(DATE_ATOM) . "\n";
