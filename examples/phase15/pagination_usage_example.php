<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 17:00
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

// Load composer and stubs
require_once __DIR__ . '/../../tests/bootstrap.php';

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Generic\GenericMySQLRepository;

// 1. Define a Concrete Repository
class UserPaginationRepository extends GenericMySQLRepository
{
    public function __construct(AdapterInterface $adapter)
    {
        parent::__construct($adapter);
        $this->tableName = 'users';
    }
}

// 2. Mock Adapter and Driver (using SQLite for example)
$pdo = new PDO('sqlite::memory:');
$pdo->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, name TEXT, email TEXT)');

// Seed 50 users
$stmt = $pdo->prepare('INSERT INTO users (id, name, email) VALUES (:id, :name, :email)');
for ($i = 1; $i <= 50; $i++) {
    $stmt->execute([
        ':id' => $i,
        ':name' => "User $i",
        ':email' => "user$i@example.com"
    ]);
}

// Create a Fake Adapter implementation
$adapter = new class ($pdo) implements AdapterInterface {
    public function __construct(private PDO $pdo)
    {
    }
    public function getDriver(): PDO
    {
        return $this->pdo;
    }
    public function getType(): string
    {
        return 'mysql';
    }
    public function isConnected(): bool
    {
        return true;
    }
    public function connect(): void
    {
    }
    public function disconnect(): void
    {
    }
    public function getConnection(): PDO
    {
        return $this->pdo;
    }
    public function healthCheck(): bool
    {
        return true;
    }
};

// 3. Instantiate Repository
$repo = new UserPaginationRepository($adapter);

// 4. Demonstrate Usage

echo "--- Basic Pagination (Page 1, 10 per page) ---\n";
$result1 = $repo->paginate(1, 10);
echo 'Current Page: ' . $result1->pagination->page . "\n";
echo 'Total Items: ' . $result1->pagination->total . "\n";
echo 'Total Pages: ' . $result1->pagination->totalPages . "\n";
echo 'Items Count: ' . count($result1->data) . "\n";
echo 'Has Next: ' . ($result1->pagination->hasNext ? 'Yes' : 'No') . "\n\n";

echo "--- Pagination with Filters (Page 1, 5 per page) ---\n";
// Filtering by name 'User 1' (should match only one)
$result2 = $repo->paginateBy(['name' => 'User 1'], 1, 5);
echo 'Filtered Total: ' . $result2->pagination->total . "\n";
/** @var array<string, mixed> $user */
$user = $result2->data[0] ?? [];
echo 'Found User: ' . ($user['name'] ?? 'None') . "\n";
