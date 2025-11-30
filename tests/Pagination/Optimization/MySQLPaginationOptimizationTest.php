<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 18:35
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Pagination\Optimization;

use Maatify\DataRepository\Generic\GenericMySQLRepository;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class MySQLPaginationOptimizationTest extends TestCase
{
    public function testPaginateUsesLimitAndOffset(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmtCount = $this->createMock(PDOStatement::class);
        $stmtFetch = $this->createMock(PDOStatement::class);

        // Expect Count Query
        $stmtCount->method('fetchColumn')->willReturn(100);

        // Expect Fetch Query with LIMIT and OFFSET
        $stmtFetch->method('fetchAll')->willReturn([]);

        $pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnCallback(function (mixed $sql) use ($stmtCount, $stmtFetch) {
                if (! is_string($sql)) {
                    throw new \Exception('Expected string sql');
                }
                if (str_contains($sql, 'COUNT(*)')) {
                    return $stmtCount;
                }

                // Verify Optimization: SQL must contain LIMIT and OFFSET
                if (str_contains($sql, 'LIMIT 10') && str_contains($sql, 'OFFSET 20')) {
                    return $stmtFetch;
                }

                throw new \Exception("Query did not contain expected optimization clauses: $sql");
            });

        $mockAdapter = $this->createMock(\Maatify\Common\Contracts\Adapter\AdapterInterface::class);

        $repo = new class($mockAdapter, $pdo) extends GenericMySQLRepository {
            protected string $tableName = 'test_table';

            public function __construct(\Maatify\Common\Contracts\Adapter\AdapterInterface $adapter, private PDO $pdo)
            {
                parent::__construct($adapter);
            }

            protected function getDriver(): object
            {
                return $this->pdo;
            }
        };

        // Request Page 3, 10 items per page (Offset = 20)
        $repo->paginate(3, 10);
    }
}
