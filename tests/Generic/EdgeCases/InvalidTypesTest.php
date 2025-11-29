<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 08:35
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\EdgeCases;

use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use PHPUnit\Framework\TestCase;
use PDO;
use stdClass;

class InvalidTypesTest extends TestCase
{
    private $pdo;
    private $repository;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);

        $this->repository = new class($this->pdo) extends GenericMySQLRepository {
            protected string $tableName = 'test_table';
        };
    }

    public function testInsertWithObjectThrowsRepositoryException(): void
    {
        $data = ['id' => 1, 'info' => new stdClass()];

        $stmt = $this->createMock(\PDOStatement::class);
        $this->pdo->method('prepare')->willReturn($stmt);
        $stmt->method('execute')->willThrowException(new \PDOException('Invalid parameter type'));

        $this->expectException(RepositoryException::class);
        $this->repository->insert($data);
    }
}
