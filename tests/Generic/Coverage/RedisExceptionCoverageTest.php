<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-30 11:20:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Generic\Coverage;

use Maatify\DataRepository\Generic\GenericRedisRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class MockRedisRepo extends GenericRedisRepository
{
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->ops = new \Maatify\DataRepository\Generic\Support\RedisOps($client);
    }

    // Expose protected methods for testing
    public function matches(array $item, array $filters): bool
    {
        return parent::matches($item, $filters);
    }
}

class RedisExceptionCoverageTest extends TestCase
{
    private MockObject $client;
    private MockRedisRepo $repo;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->repo = new MockRedisRepo($this->client);
    }

    public function testFindReturnsNullIfNotFound(): void
    {
        $this->client->method('__call')->with('get', $this->anything())->willReturn(null);
        $this->assertNull($this->repo->find(1));
    }

    public function testInsertGeneratesIdIfMissing(): void
    {
        // Mock SET to return OK
        $this->client->method('__call')->with('set', $this->anything())->willReturn(true);

        $id = $this->repo->insert(['name' => 'test']);
        $this->assertNotEmpty($id);
        $this->assertIsString($id);
    }

    public function testUpdateReturnsFalseIfEmptyData(): void
    {
        $this->assertFalse($this->repo->update(1, []));
    }

    public function testUpdateReturnsFalseIfNotFound(): void
    {
        $this->client->method('__call')->with('get', $this->anything())->willReturn(null);
        $this->assertFalse($this->repo->update(1, ['name' => 'updated']));
    }

    public function testFindOneByReturnsNullIfEmpty(): void
    {
        // Mock keys to return empty
        $this->client->method('__call')->with('keys', $this->anything())->willReturn([]);

        $this->assertNull($this->repo->findOneBy(['name' => 'test']));
    }

    public function testMatchesLogic(): void
    {
        $item = ['id' => 1, 'name' => 'test', 'active' => true];

        $this->assertTrue($this->repo->matches($item, ['name' => 'test']));
        $this->assertTrue($this->repo->matches($item, ['name' => 'test', 'active' => true]));
        $this->assertFalse($this->repo->matches($item, ['name' => 'other']));
        $this->assertFalse($this->repo->matches($item, ['missing' => 'val']));
    }
}
