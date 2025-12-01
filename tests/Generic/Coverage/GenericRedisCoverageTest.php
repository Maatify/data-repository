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
    /** @var GenericRedisRepository&MockObject */
    private GenericRedisRepository $repository;

    /** @var AdapterInterface&MockObject */
    private AdapterInterface $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);

        // We use a partial mock but don't mock findBy/findOneBy to test their new logic
        /** @var GenericRedisRepository&MockObject $repo */
        $repo = $this->getMockBuilder(GenericRedisRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRedisOps'])
            ->getMock();

        $this->repository = $repo;

        $refAdapter = new \ReflectionProperty(GenericRedisRepository::class, 'adapter');
        $refAdapter->setAccessible(true);
        $refAdapter->setValue($this->repository, $this->adapter);
    }

    // Phase 19: findBy / findOneBy / paginateBy no longer throw exceptions.
    // We removed those tests.

    public function testCountWithFiltersThrowsException(): void
    {
        // This is STILL unsupported in the current implementation of count()
        // Phase 19 implementation only touched findBy/paginateBy logic.
        // Let's check the code: GenericRedisRepository::count() still has check:
        // if (! empty($filters)) { throw new RepositoryException... }
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Filtering count is not supported');
        $this->repository->count(['a' => 1]);
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
