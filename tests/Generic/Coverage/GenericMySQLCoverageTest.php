<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 02:58
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Coverage;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Exceptions\QueryExecutionException;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenericMySQLCoverageTest extends TestCase
{
    /** @var GenericMySQLRepository<object>&MockObject */
    private GenericMySQLRepository $repository;

    /** @var AdapterInterface&MockObject */
    private AdapterInterface $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);

        /** @var GenericMySQLRepository<object>&MockObject $repo */
        $repo = $this->getMockBuilder(GenericMySQLRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDriver'])
            ->getMock();

        $this->repository = $repo;

        // Initialize properties normally set in constructor
        $refAdapter = new \ReflectionProperty(GenericMySQLRepository::class, 'adapter');
        $refAdapter->setAccessible(true);
        $refAdapter->setValue($this->repository, $this->adapter);

        $refTable = new \ReflectionProperty(GenericMySQLRepository::class, 'tableName');
        $refTable->setAccessible(true);
        $refTable->setValue($this->repository, 'test_table');
    }

    public function testUpdateReturnsFalseOnEmptyData(): void
    {
        // Must bypass getPdo check as it shouldn't be called
        $result = $this->repository->update(1, []);
        $this->assertFalse($result);
    }

    public function testGetPdoThrowsExceptionWhenDriverIsNotPdo(): void
    {
        $this->repository->expects($this->once())
            ->method('getDriver')
            ->willReturn(new \stdClass()); // Not PDO

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('GenericMySQLRepository requires a PDO driver or compatible wrapper.');

        $this->repository->find(1);
    }

    public function testGetPdoRecognizesDbalWrapper(): void
    {
        $pdo = $this->createMock(\PDO::class);
        $dbalDriver = new class ($pdo) {
            private \PDO $pdo;
            public function __construct(\PDO $pdo)
            {
                $this->pdo = $pdo;
            }
            public function getNativeConnection(): \PDO
            {
                return $this->pdo;
            }
        };

        $this->repository->expects($this->any())
            ->method('getDriver')
            ->willReturn($dbalDriver);

        // We expect prepare to be called on the inner PDO
        $stmt = $this->createMock(\PDOStatement::class);
        $pdo->expects($this->once())->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())->method('execute');
        $stmt->expects($this->once())->method('fetch')->willReturn(false);

        $this->repository->find(1);
    }

    public function testCrudMethodsThrowRepositoryExceptionOnPdoFailure(): void
    {
        $pdo = $this->createMock(\PDO::class);
        $this->repository->expects($this->any())
            ->method('getDriver')
            ->willReturn($pdo);

        $pdo->expects($this->any())
            ->method('prepare')
            ->willThrowException(new \PDOException('Simulated Failure'));

        // Test Find
        try {
            $this->repository->find(1);
            $this->fail('Expected QueryExecutionException not thrown');
        } catch (QueryExecutionException $e) {
            $this->assertSame('Find operation failed.', $e->getMessage());
        }

        // Test FindBy
        try {
            $this->repository->findBy(['col' => 'val']);
            $this->fail('Expected QueryExecutionException not thrown');
        } catch (QueryExecutionException $e) {
            $this->assertSame('FindBy operation failed.', $e->getMessage());
        }

        // Test Count
        try {
            $this->repository->count();
            $this->fail('Expected QueryExecutionException not thrown');
        } catch (QueryExecutionException $e) {
            $this->assertSame('Count operation failed.', $e->getMessage());
        }

        // Test Insert
        try {
            $this->repository->insert(['col' => 'val']);
            $this->fail('Expected QueryExecutionException not thrown');
        } catch (QueryExecutionException $e) {
            $this->assertSame('Insert operation failed.', $e->getMessage());
        }

        // Test Update
        try {
            $this->repository->update(1, ['col' => 'val']);
            $this->fail('Expected QueryExecutionException not thrown');
        } catch (QueryExecutionException $e) {
            $this->assertSame('Update operation failed.', $e->getMessage());
        }

        // Test Delete
        try {
            $this->repository->delete(1);
            $this->fail('Expected QueryExecutionException not thrown');
        } catch (QueryExecutionException $e) {
            $this->assertSame('Delete operation failed.', $e->getMessage());
        }
    }
}
