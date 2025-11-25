<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 03:10
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Fake;

use Maatify\DataFakes\Adapters\Redis\FakeRedisAdapter;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use PHPUnit\Framework\TestCase;

class GenericRedisRepositoryFakeTest extends TestCase
{
    private GenericRedisRepository $repo;

    protected function setUp(): void
    {
        // 1. Init Fake Redis Adapter (No constructor args)
        $adapter = new FakeRedisAdapter();

        // 2. Init Repository
        $this->repo = new class ($adapter) extends GenericRedisRepository {
            protected string $keyPrefix = 'test:';
        };
    }

    public function testInsertAndFind(): void
    {
        $id = $this->repo->insert(['id' => 'u1', 'name' => 'Redis User']);
        $this->assertEquals('u1', $id);

        $result = $this->repo->find('u1');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('Redis User', $result['name']);
    }

    public function testUpdate(): void
    {
        $this->repo->insert(['id' => 'u1', 'score' => 10]);

        $updated = $this->repo->update('u1', ['score' => 20]);
        $this->assertTrue($updated);

        $result = $this->repo->find('u1');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('score', $result);
        $this->assertEquals(20, $result['score']);
    }

    public function testDelete(): void
    {
        $this->repo->insert(['id' => 'u1', 'val' => 1]);

        $this->assertTrue($this->repo->delete('u1'));
        $this->assertNull($this->repo->find('u1'));
    }
}
