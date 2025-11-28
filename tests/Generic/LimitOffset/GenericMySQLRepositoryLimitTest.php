<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 05:40
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\LimitOffset;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use PHPUnit\Framework\TestCase;

class GenericMySQLRepositoryLimitTest extends TestCase
{
    private GenericMySQLRepository $repo;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');

        for ($i = 1; $i <= 10; $i++) {
            $pdo->exec("INSERT INTO users (id, name) VALUES ($i, 'User $i')");
        }

        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->method('getDriver')->willReturn($pdo);

        $this->repo = new class ($adapter) extends GenericMySQLRepository {
            protected string $tableName = 'users';
        };
    }

    public function testLimit(): void
    {
        $results = $this->repo->findBy([], null, 3);
        $this->assertCount(3, $results);
    }

    public function testOffset(): void
    {
        $results = $this->repo->findBy([], ['id' => 'ASC'], 3, 5);
        $this->assertCount(3, $results);
        // Offset 5 means we skip 1..5, so 6, 7, 8
        $this->assertEquals(6, $results[0]['id']);
    }

    public function testInvalidLimit(): void
    {
        $this->expectException(RepositoryException::class);
        $this->repo->findBy([], null, 0);
    }

    public function testInvalidOffset(): void
    {
        $this->expectException(RepositoryException::class);
        $this->repo->findBy([], null, null, -1);
    }
}
