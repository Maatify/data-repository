<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Coverage;

use Maatify\DataAdapters\Adapters\RedisAdapter;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use Maatify\DataRepository\Tests\Helpers\RealAdapterTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(GenericRedisRepository::class)]
#[Group('coverage')]
class GenericRedisRepositoryCoverageTest extends TestCase
{
    use RealAdapterTrait;

    private GenericRedisRepository $repo;
    private RedisAdapter $adapter;

    protected function setUp(): void
    {
        if (!class_exists(RedisAdapter::class) || !extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension or Adapter not installed');
        }

        $config = $this->getRealConfig();
        $this->adapter = new RedisAdapter($config, 'main');

        try {
            $this->adapter->connect();
        } catch (\Exception $e) {
            $this->markTestSkipped('Could not connect to Redis: ' . $e->getMessage());
        }

        $this->repo = new class ($this->adapter) extends GenericRedisRepository {
            protected string $keyPrefix = 'generic_coverage_test:';
        };
    }

    protected function tearDown(): void
    {
        if (isset($this->adapter)) {
            $this->adapter->getDriver()->flushdb();
        }
        parent::tearDown();
    }

    public function testAdd(): void
    {
        $this->repo->insert(['id' => 'c1', 'name' => 'Coverage 1']);
        $data = $this->repo->find('c1');
        $this->assertNotNull($data);
        $this->assertEquals('Coverage 1', $data['name']);
    }

    public function testUpdate(): void
    {
        $this->repo->insert(['id' => 'c2', 'name' => 'Coverage 2']);
        $this->repo->update('c2', ['name' => 'Coverage 2 Updated']);
        $data = $this->repo->find('c2');
        $this->assertNotNull($data);
        $this->assertEquals('Coverage 2 Updated', $data['name']);
    }

    public function testDelete(): void
    {
        $this->repo->insert(['id' => 'c3', 'name' => 'Coverage 3']);
        $this->repo->delete('c3');
        $data = $this->repo->find('c3');
        $this->assertNull($data);
    }

    public function testCount(): void
    {
        $this->repo->insert(['id' => 'c4', 'name' => 'Coverage 4']);
        $this->repo->insert(['id' => 'c5', 'name' => 'Coverage 5']);
        $this->assertEquals(2, $this->repo->count());
    }

    public function testListAll(): void
    {
        $this->repo->insert(['id' => 'c6', 'name' => 'Coverage 6']);
        $this->repo->insert(['id' => 'c7', 'name' => 'Coverage 7']);
        $all = $this->repo->findAll();
        $this->assertCount(2, $all);
    }

    public function testFindBy(): void
    {
        $this->repo->insert(['id' => 'c8', 'name' => 'FindMe', 'type' => 'test']);
        $this->repo->insert(['id' => 'c9', 'name' => 'FindMe', 'type' => 'test']);
        $results = $this->repo->findBy(['name' => 'FindMe']);
        $this->assertCount(2, $results);
    }

    public function testFindOneBy(): void
    {
        $this->repo->insert(['id' => 'c10', 'name' => 'FindOne', 'type' => 'unique']);
        $result = $this->repo->findOneBy(['type' => 'unique']);
        $this->assertNotNull($result);
        $this->assertEquals('c10', $result['id']);
    }
}
