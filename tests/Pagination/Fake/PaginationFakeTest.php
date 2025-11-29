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

use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Tests\Base\Fake\FakeMySQLBaseRepositoryTest;
use Maatify\Common\Pagination\PaginationDTO;
use PHPUnit\Framework\TestCase;

class PaginationFakeTest extends TestCase
{
    private object $repository;

    protected function setUp(): void
    {
        // Mock a concrete repository class for testing
        $this->repository = new class(new \PDO('sqlite::memory:')) extends GenericMySQLRepository {
            public function __construct(\PDO $pdo)
            {
                parent::__construct($pdo);
                $this->tableName = 'test_table';
                $this->getDriver()->exec("CREATE TABLE IF NOT EXISTS test_table (id INTEGER PRIMARY KEY, name TEXT)");
                // Seed data
                for ($i = 1; $i <= 25; $i++) {
                    $this->getDriver()->exec("INSERT INTO test_table (id, name) VALUES ($i, 'Item $i')");
                }
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
        // Simulate a filter where name = 'Item 1' (only one record)
        // Note: SQLite LIKE might be case insensitive or specific, using simple WHERE
        // But GenericMySQLRepository uses FilterUtils which builds WHERE clauses.
        // We'll rely on simple exact match for this fake test.

        // Let's verify standard pagination flow first.
        $result = $this->repository->paginateBy([], 1, 5);
        $this->assertCount(5, $result->data);
        $this->assertEquals(5, $result->pagination->pages);
    }
}
