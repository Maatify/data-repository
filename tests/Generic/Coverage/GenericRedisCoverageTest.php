<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 03:05
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Coverage;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenericRedisCoverageTest extends TestCase
{
    private MockObject|GenericRedisRepository $repository;
    private MockObject $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);

        $this->repository = $this->getMockBuilder(GenericRedisRepository::class)
            ->setConstructorArgs([$this->adapter])
            ->onlyMethods(['getRedisOps'])
            ->getMock();
    }

    public function testUnsupportedMethodsThrowException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('findBy() is not supported');
        $this->repository->findBy([]);
    }

    public function testFindOneByThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('findOneBy() is not supported');
        $this->repository->findOneBy([]);
    }

    public function testPaginateByThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('paginateBy() with filters is not supported');
        $this->repository->paginateBy([], 1, 10);
    }

    public function testCountWithFiltersThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Filtering count is not supported');
        $this->repository->count(['a'=>1]);
    }

    public function testInsertThrowsExceptionIfIdMissing(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Generic Redis Insert requires 'id'");
        $this->repository->insert(['name' => 'test']);
    }

    public function testInsertThrowsExceptionIfIdInvalidType(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Generic Redis Insert 'id' must be int|string");
        $this->repository->insert(['id' => 1.5]);
    }
}
