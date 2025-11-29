<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 08:32
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\EdgeCases;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class PartialUpdateTest extends TestCase
{
    /** @var PDO&MockObject */
    private $pdo;
    /** @var PDOStatement&MockObject */
    private $stmt;
    private GenericMySQLRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);

        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->method('getDriver')->willReturn($this->pdo);

        $this->repository = new class($adapter) extends GenericMySQLRepository {
            protected string $tableName = 'test_table';
        };
    }

    public function testPartialUpdateDoesNotAffectOtherFields(): void
    {
        $id = 1;
        // Only updating 'name', expecting 'description' to be untouched in the query
        $data = ['name' => 'Updated Name'];

        // The query should only contain the updated field
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains("UPDATE `test_table` SET `name` = :name WHERE"))
            ->willReturn($this->stmt);

        $this->stmt->expects($this->once())
            ->method('execute')
            ->with(['name' => 'Updated Name', 'primaryKey' => 1])
            ->willReturn(true);

        $result = $this->repository->update($id, $data);
        $this->assertTrue($result);
    }

    public function testEmptyUpdateReturnsFalse(): void
    {
        $id = 1;
        $data = [];

        $this->pdo->expects($this->never())->method('prepare');

        $result = $this->repository->update($id, $data);
        $this->assertFalse($result);
    }
}
