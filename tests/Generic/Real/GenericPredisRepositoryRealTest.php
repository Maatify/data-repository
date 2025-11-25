<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 03:14
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Real;

use Maatify\DataAdapters\Adapters\PredisAdapter;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use Maatify\DataRepository\Tests\Helpers\RealAdapterTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
class GenericPredisRepositoryRealTest extends TestCase
{
    use RealAdapterTrait;

    public function testCrudOperationsViaPredis(): void
    {
        if (! class_exists(PredisAdapter::class) || ! class_exists(\Predis\Client::class)) {
            $this->markTestSkipped('Predis Adapter not installed');
        }

        $config = $this->getRealConfig();
        $adapter = new PredisAdapter($config, 'main');

        try {
            $adapter->connect();
        } catch (\Exception $e) {
            $this->markTestSkipped('Could not connect to Redis via Predis: ' . $e->getMessage());
        }

        $repo = new class ($adapter) extends GenericRedisRepository {
            protected string $keyPrefix = 'generic_predis_test:';
        };

        // 1. Insert
        // GenericRedisRepository calls $driver->set($key, $val)
        // Predis Client must support this signature.
        $repo->insert(['id' => 'p1', 'name' => 'Predis User']);

        // 2. Find
        // GenericRedisRepository calls $driver->get($key)
        $result = $repo->find('p1');
        $this->assertNotNull($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('Predis User', $result['name']);

        // 3. Update
        $repo->update('p1', ['name' => 'Predis Updated']);
        $result = $repo->find('p1');
        $this->assertNotNull($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('Predis Updated', $result['name']);

        // 4. Delete
        // GenericRedisRepository calls $driver->del($key)
        $repo->delete('p1');
        $this->assertNull($repo->find('p1'));
    }
}
