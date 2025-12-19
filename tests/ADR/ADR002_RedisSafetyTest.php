<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-19
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\ADR;

use Maatify\DataFakes\Adapters\Redis\FakeRedisAdapter;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use PHPUnit\Framework\TestCase;

/**
 * @group ADR
 * @group ADR-002
 * @group ADR-006
 */
class ADR002_RedisSafetyTest extends TestCase
{
    /** @var GenericRedisRepository<object> */
    private GenericRedisRepository $repo;

    protected function setUp(): void
    {
        $adapter = new FakeRedisAdapter();
        $this->repo = new class ($adapter) extends GenericRedisRepository {
            protected string $keyPrefix = 'adr_test:';
        };
    }

    /**
     * @test
     * @description ADR-002 prohibits query-like counting (filtering) on Redis.
     */
    public function testCountWithFiltersThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Filtering count is not supported in Redis.');

        // This operation mimics "SELECT count(*) WHERE ...", which is unsafe in Redis
        $this->repo->count(['some_field' => 'value']);
    }

    /**
     * @test
     * @description ADR-002 prohibits "inserting" data without a Key (ID).
     */
    public function testInsertRequiresExplicitId(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Generic Redis Insert requires 'id'");

        // Relational thinking: "ID will be auto-generated" -> Banned in Redis Repo
        $this->repo->insert(['name' => 'No ID']);
    }

    /**
     * @test
     * @description ADR-002/006 prohibits undefined or complex ID types.
     */
    public function testInsertEnforcesIdType(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Generic Redis Insert 'id' must be int|string");

        // Object as ID -> Unsafe/undefined behavior
        $this->repo->insert(['id' => new \stdClass(), 'name' => 'Bad ID']);
    }
}
