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

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use PHPUnit\Framework\TestCase;

class RedisMissingCoverageTest extends TestCase
{
    private object $redisMock;
    /** @var GenericRedisRepository<object> */
    private GenericRedisRepository $repo;

    protected function setUp(): void
    {
        // Mock Predis Client that returns empty array for keys to prevent TypeError in findAll
        // Replaced deprecated addMethods with anonymous class
        $this->redisMock = new class {
            /** @return array<mixed> */
            public function keys(string $pattern): array
            {
                return [];
            }

            // Allow other calls if necessary (though this test might only need keys)
            public function __call(string $method, array $args): mixed
            {
                return null;
            }
        };

        $adapter = new FakeRedisAdapterSatisfying($this->redisMock);

        $this->repo = new class ($adapter) extends GenericRedisRepository {
            protected string $keyPrefix = 'test:';
        };
    }

    // Phase 19: Removed findBy/findOneBy exception tests as they are now supported.

    public function testCountWithFiltersThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Filtering count is not supported in Redis');
        $this->repo->count(['id' => 1]);
    }
}

class FakeRedisAdapterSatisfying implements AdapterInterface
{
    public function __construct(private object $driver)
    {
    }

    /** @return mixed */
    public function getDriver(): mixed
    {
        return $this->driver;
    }

    public function getType(): string
    {
        return 'redis';
    }

    public function connect(): void
    {
    }
    public function isConnected(): bool
    {
        return true;
    }
    public function disconnect(): void
    {
    }
    /** @return mixed */
    public function getConnection(): mixed
    {
        return $this->driver;
    }
    public function debugConfig(): object
    {
        return (object)[];
    }
    public function healthCheck(): bool
    {
        return true;
    }
}
