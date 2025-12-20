<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 05:50
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\LimitOffset;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class GenericMySQLRepositoryLimitTest extends TestCase
{
    /** @var GenericMySQLRepository<object>&MockObject */
    private GenericMySQLRepository $repo;
    /** @var PDO&MockObject */
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->method('getDriver')->willReturn($this->pdo);

        /** @var GenericMySQLRepository<object>&MockObject $repo */
        $repo = $this->getMockBuilder(GenericMySQLRepository::class)
            ->setConstructorArgs([$adapter])
            ->onlyMethods(['getMysqlOps']) // Keep real findBy
            ->getMock();

        $this->repo = $repo;

        // Set table name via reflection or similar if needed, or assume default empty string is OK for these tests
        $ref = new \ReflectionProperty(GenericMySQLRepository::class, 'tableName');
        $ref->setAccessible(true);
        $ref->setValue($this->repo, 'test');
    }

    public function testPaginateByValidatesLimit(): void
    {
        $this->expectException(RepositoryException::class);

        // Setup mock for the count query that happens before validation
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetchColumn')->willReturn(0);

        // Expect prepare call for count(*)
        $this->pdo->method('prepare')
            ->willReturn($stmt);

        // Exceed default max limit (10000)
        $this->repo->paginateBy([], 1, 10001);
    }

    public function testFindByAppendsLimitAndOffsetToSql(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetchAll')->willReturn([]);

        // Ensure execute doesn't crash on false by forcing a return value
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('LIMIT 5 OFFSET 10'))
            ->willReturn($stmt);

        $this->repo->findBy([], null, 5, 10);
    }
}
