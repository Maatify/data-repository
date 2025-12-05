<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-02
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Hydration\BaseHydrator;

// 1. Define Entity
class UserEntity
{
    public int $id;
    public string $name;
    public string $role;
}

// 2. Define Hydrator
/**
 * @extends BaseHydrator<UserEntity>
 */
class UserHydrator extends BaseHydrator
{
    protected function createInstance(): object
    {
        return new UserEntity();
    }
}

// 3. Define Repository
/**
 * @extends GenericMySQLRepository<UserEntity>
 */
class UserRepository extends GenericMySQLRepository
{
    protected string $tableName = 'users';
}

// 4. Mock Adapter (for example purpose)
/** @var AdapterInterface $adapter */
$adapter = new class implements AdapterInterface {
    public function getDriver(): PDO
    {
        return new PDO('sqlite::memory:');
    }
    public function getType(): string { return 'mysql'; }
    public function connect(): void {}
    public function isConnected(): bool { return true; }
    public function disconnect(): void {}
    public function getConnection(): mixed { return null; }
    public function healthCheck(): bool { return true; }
};

// 5. Usage
$repo = new UserRepository($adapter);
$repo->setHydrator(new UserHydrator());

/*
 * Static Analysis Benefits:
 * PHPStan now knows that $user is UserEntity|null.
 * No need for explicit @var casting in application code.
 */
$user = $repo->findObject(1);

if ($user) {
    echo "Found User: " . $user->name . "\n";
}

/*
 * Collections are also typed: array<UserEntity>
 */
$users = $repo->findObjectsBy(['role' => 'admin']);
foreach ($users as $u) {
    echo "Admin: " . $u->name . "\n";
}
