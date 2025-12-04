<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-04 10:09
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Examples\Phase2;

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Base\BaseRepository;

// Mock Adapter
$adapter = new class () implements AdapterInterface {
    public function connect(): void
    {
    }
    public function isConnected(): bool
    {
        return true;
    }
    public function getConnection(): object
    {
        return new \stdClass();
    }
    public function healthCheck(): bool
    {
        return true;
    }
    public function disconnect(): void
    {
    }
    public function getDriver(): object
    {
        return new \stdClass();
    }
    public function getType(): string
    {
        return 'mock';
    }
};

// Create a concrete repository from the abstract BaseRepository
class ExampleRepository extends BaseRepository
{
    protected string $tableName = 'example_table';

    public function getTable(): string
    {
        return $this->getTableName();
    }

    public function find(int|string $id): ?array
    {
        // TODO: Implement find() method.
        return ['columns' => 'foo,bar'];
    }

    public function findBy(array $filters): array
    {
        // TODO: Implement findBy() method.
        return [['columns' => 'foo,bar']];
    }

    public function findAll(): array
    {
        // TODO: Implement findAll() method.
        return [['columns' => 'foo,bar']];
    }

    public function insert(array $data): int|string
    {
        // TODO: Implement insert() method.
        return 1;
    }

    public function update(int|string $id, array $data): bool
    {
        // TODO: Implement update() method.
        return true;
    }

    public function delete(int|string $id): bool
    {
        // TODO: Implement delete() method.
        return true;
    }
}

$repo = new ExampleRepository($adapter);

echo 'Repository Table: ' . $repo->getTable() . "\n";
