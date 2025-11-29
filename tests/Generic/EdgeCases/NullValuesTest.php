<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 08:30
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

class NullValuesTest extends TestCase
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

        $this->repository = new class ($adapter) extends GenericMySQLRepository {
            protected string $tableName = 'test_table';
        };
    }

    public function testInsertWithNullValues(): void
    {
        $data = ['id' => 1, 'name' => 'Test', 'description' => null];

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt->expects($this->once())
            ->method('execute')
            ->with($data);

        $this->pdo->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('1');

        $result = $this->repository->insert($data);
        $this->assertEquals(1, $result);
    }

    public function testUpdateWithNullValues(): void
    {
        $id = 1;
        $data = ['description' => null];
        $expectedData = ['description' => null, 'primaryKey' => $id];

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt->expects($this->once())
            ->method('execute')
            ->with($expectedData)
            ->willReturn(true);

        $result = $this->repository->update($id, $data);
        $this->assertTrue($result);
    }
}
