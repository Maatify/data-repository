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
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use Maatify\DataRepository\Generic\Support\RedisOps;
use PHPUnit\Framework\TestCase;

class GenericRedisRepositoryFakeTest extends TestCase
{
    /**
     * @var GenericRedisRepository&object
     * @phpstan-var GenericRedisRepository&object
     */
    private GenericRedisRepository $repo;

    protected function setUp(): void
    {
        // 1. Init Fake Redis Adapter (No constructor args)
        $adapter = new FakeRedisAdapter();

        // 2. Init Repository
        $this->repo = new class ($adapter) extends GenericRedisRepository {
            protected string $keyPrefix = 'test:';

            public function ops(): RedisOps
            {
                return $this->getRedisOps();
            }
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

    public function testInsertValidations(): void
    {
        $this->expectException(RepositoryException::class);
        $this->repo->insert(['name' => 'Missing ID']);

        $this->fail('Exception was not thrown for missing id');
    }

    public function testInsertWithInvalidIdType(): void
    {
        $this->expectException(RepositoryException::class);
        $this->repo->insert(['id' => ['bad'], 'name' => 'Invalid']);
    }

    public function testFindByAndFindOneByThrowExceptions(): void
    {
        $this->expectException(RepositoryException::class);
        try {
            $this->repo->findBy(['role' => 'admin']);
        } catch (RepositoryException $e) {
            // Verify message and rethrow for PHPUnit expectation
            $this->assertStringContainsString('findBy()', $e->getMessage());

            throw $e;
        }

        $this->fail('Exception not thrown for findBy');
    }

    public function testFindOneByThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->repo->findOneBy(['role' => 'admin']);
    }

    public function testCountWithFiltersThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->repo->count(['role' => 'admin']);
    }

    public function testFindReturnsNullOnInvalidJsonPayload(): void
    {
        /** @phpstan-ignore-next-line anonymous subclass exposes ops() only in this test */
        $this->repo->ops()->set('test:bad', '{invalid-json');

        $this->assertNull($this->repo->find('bad'));
    }

    public function testFindAllSkipsInvalidPayloads(): void
    {
        $this->repo->insert(['id' => 'good', 'value' => 1]);
        /** @phpstan-ignore-next-line anonymous subclass exposes ops() only in this test */
        $this->repo->ops()->set('test:broken', '{not-json');

        $results = $this->repo->findAll();

        $this->assertCount(1, $results);
        $this->assertSame('good', $results[0]['id']);
    }

    public function testCountWithoutFilters(): void
    {
        $this->repo->insert(['id' => 'u1', 'val' => 1]);
        $this->repo->insert(['id' => 'u2', 'val' => 2]);

        $this->assertSame(2, $this->repo->count());
    }

    public function testRedisOpsMemoization(): void
    {
        /** @phpstan-ignore-next-line anonymous subclass exposes ops() only in this test */
        $first = $this->repo->ops();
        /** @phpstan-ignore-next-line anonymous subclass exposes ops() only in this test */
        $second = $this->repo->ops();

        $this->assertSame($first, $second);
    }
}
