<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Exceptions;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use PDO;
use PDOException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExceptionTestEntity
{
    public int $id;
}

/**
 * @extends GenericMySQLRepository<ExceptionTestEntity>
 */
class GenericMySQLRepositoryExceptionStub extends GenericMySQLRepository
{
    private AdapterInterface $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        parent::__construct($adapter);
    }

    protected function createInstance(): object
    {
        return new ExceptionTestEntity();
    }
}

class GenericMySQLRepositoryExceptionTest extends TestCase
{
    /** @var AdapterInterface&MockObject */
    private $adapter;

    /** @var PDO&MockObject */
    private $pdo;

    private GenericMySQLRepositoryExceptionStub $repository;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);

        // We need to mock the AdapterInterface such that getDriver returns our Mock PDO
        // But GenericMySQLRepository checks for specific driver types.
        // If getDriver returns the mock PDO, it should pass verification in getPdo().

        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->adapter->method('getDriver')->willReturn($this->pdo);

        $this->repository = new GenericMySQLRepositoryExceptionStub($this->adapter);
        $this->repository->setTableName('test_table');
    }

    public function testFindThrowsRepositoryException(): void
    {
        $this->pdo->method('prepare')->willThrowException(new PDOException('Simulated PDO Error'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Find failed: Simulated PDO Error');

        $this->repository->find(1);
    }

    public function testFindByThrowsRepositoryException(): void
    {
        $this->pdo->method('prepare')->willThrowException(new PDOException('Simulated PDO Error'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('FindBy failed: Simulated PDO Error');

        $this->repository->findBy(['id' => 1]);
    }

    public function testCountThrowsRepositoryException(): void
    {
        $this->pdo->method('prepare')->willThrowException(new PDOException('Simulated PDO Error'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Count failed: Simulated PDO Error');

        $this->repository->count();
    }

    public function testInsertThrowsRepositoryException(): void
    {
        $this->pdo->method('prepare')->willThrowException(new PDOException('Simulated PDO Error'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Insert failed: Simulated PDO Error');

        $this->repository->insert(['col' => 'val']);
    }

    public function testUpdateThrowsRepositoryException(): void
    {
        $this->pdo->method('prepare')->willThrowException(new PDOException('Simulated PDO Error'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Update failed: Simulated PDO Error');

        $this->repository->update(1, ['col' => 'val']);
    }

    public function testDeleteThrowsRepositoryException(): void
    {
        $this->pdo->method('prepare')->willThrowException(new PDOException('Simulated PDO Error'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Delete failed: Simulated PDO Error');

        $this->repository->delete(1);
    }
}
