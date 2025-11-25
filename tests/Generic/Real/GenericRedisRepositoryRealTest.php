<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 03:16
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Real;

use Maatify\DataAdapters\Adapters\RedisAdapter;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use Maatify\DataRepository\Tests\Helpers\RealAdapterTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
class GenericRedisRepositoryRealTest extends TestCase
{
    use RealAdapterTrait;

    public function testCrudOperationsViaNativeRedis(): void
    {
        if (! class_exists(RedisAdapter::class) || ! extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension or Adapter not installed');
        }

        $config = $this->getRealConfig();
        $adapter = new RedisAdapter($config, 'main');

        try {
            $adapter->connect();
        } catch (\Exception $e) {
            $this->markTestSkipped('Could not connect to Redis: ' . $e->getMessage());
        }

        $repo = new class ($adapter) extends GenericRedisRepository {
            protected string $keyPrefix = 'generic_real_test:';
        };

        // 1. Insert
        $id = $repo->insert(['id' => 'u1', 'name' => 'Redis Real']);
        $this->assertEquals('u1', $id);

        // 2. Find
        $data = $repo->find('u1');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('Redis Real', $data['name']);

        // 3. Update
        $repo->update('u1', ['name' => 'Redis Updated']);
        $data = $repo->find('u1');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('Redis Updated', $data['name']);

        // 4. Delete
        $repo->delete('u1');
        $this->assertNull($repo->find('u1'));
    }
}
