<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 16:45
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Pagination\Fake;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\Common\Pagination\PaginationDTO;
use PHPUnit\Framework\TestCase;

class PaginationFakeTest extends TestCase
{
    private GenericMySQLRepository $repository;

    protected function setUp(): void
    {
        // Mock the AdapterInterface
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec("CREATE TABLE IF NOT EXISTS test_table (id INTEGER PRIMARY KEY, name TEXT)");
        // Seed data
        for ($i = 1; $i <= 25; $i++) {
            $pdo->exec("INSERT INTO test_table (id, name) VALUES ($i, 'Item $i')");
        }

        // Create an anonymous class implementing AdapterInterface
        $adapter = new class($pdo) implements AdapterInterface {
            private \PDO $pdo;

            public function __construct(\PDO $pdo)
            {
                $this->pdo = $pdo;
            }

            public function getDriver(): mixed
            {
                return $this->pdo;
            }

            public function getType(): string
            {
                return 'mysql'; // Pretend to be MySQL for the repository
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
        };

        // Create anonymous repository extending GenericMySQLRepository
        $this->repository = new class($adapter) extends GenericMySQLRepository {
            public function __construct(AdapterInterface $adapter)
            {
                parent::__construct($adapter);
                $this->tableName = 'test_table';
            }
        };
    }

    public function testPaginateFirstPage(): void
    {
        $result = $this->repository->paginate(1, 10);

        $this->assertCount(10, $result->data);
        $this->assertInstanceOf(PaginationDTO::class, $result->pagination);
        $this->assertEquals(1, $result->pagination->page);
        $this->assertEquals(10, $result->pagination->limit);
        $this->assertEquals(25, $result->pagination->total);
        $this->assertEquals(3, $result->pagination->pages); // 25 / 10 = 2.5 -> 3
    }

    public function testPaginateSecondPage(): void
    {
        $result = $this->repository->paginate(2, 10);

        $this->assertCount(10, $result->data);
        $this->assertEquals(2, $result->pagination->page);
        // SQLite doesn't guarantee order without ORDER BY, but usually insertion order holds
        // Item 11 should be at index 0 of page 2
        // $result->data is array<int, array>
        $this->assertEquals('Item 11', $result->data[0]['name']);
    }

    public function testPaginateLastPage(): void
    {
        $result = $this->repository->paginate(3, 10);

        $this->assertCount(5, $result->data); // 25 total, 20 shown, 5 remaining
        $this->assertEquals(3, $result->pagination->page);
    }

    public function testPaginateByWithFilters(): void
    {
        // Simple filter test
        // SQLite support for named params matches MySQL
        // FilterUtils generates `name = :name`

        $filters = ['name' => 'Item 1'];
        $result = $this->repository->paginateBy($filters, 1, 5);

        $this->assertCount(1, $result->data);
        $this->assertEquals(1, $result->pagination->total);
        $this->assertEquals('Item 1', $result->data[0]['name']);
    }
}
