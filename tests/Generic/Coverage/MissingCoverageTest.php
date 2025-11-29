<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 09:30
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Coverage;

use Maatify\DataFakes\Adapters\Redis\FakeRedisAdapter;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use PHPUnit\Framework\TestCase;

class MissingCoverageTest extends TestCase
{
    private GenericRedisRepository $repo;

    protected function setUp(): void
    {
        $adapter = new FakeRedisAdapter();

        $this->repo = new class ($adapter) extends GenericRedisRepository {
            protected string $keyPrefix = 'test:';
        };
    }

    public function testFindByThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('findBy() is not supported in GenericRedisRepository');
        $this->repo->findBy(['id' => 1]);
    }

    public function testFindOneByThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('findOneBy() is not supported in GenericRedisRepository');
        $this->repo->findOneBy(['id' => 1]);
    }

    public function testCountWithFiltersThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Filtering count is not supported in Redis');
        $this->repo->count(['id' => 1]);
    }
}
