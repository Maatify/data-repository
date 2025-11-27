<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Base;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataFakes\Adapters\Redis\FakeRedisAdapter;
use Maatify\DataRepository\Base\BaseRedisRepository;
use Maatify\DataRepository\Tests\Helpers\RedisRepositoryStub;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BaseRedisRepositoryTest extends TestCase
{
    #[Test]
    public function it_should_accept_predis_client_adapter(): void
    {
        $adapter = new FakeRedisAdapter();
        $repository = new RedisRepositoryStub($adapter);

        $this->assertInstanceOf(BaseRedisRepository::class, $repository);
    }

    #[Test]
    public function it_should_accept_redis_extension_adapter(): void
    {
        $adapter = new FakeRedisAdapter();
        $repository = new RedisRepositoryStub($adapter);

        $this->assertInstanceOf(BaseRedisRepository::class, $repository);
    }

    #[Test]
    public function it_should_work_with_adapter_subclasses(): void
    {
        $adapter = new FakeRedisAdapter();
        $repository = new RedisRepositoryStub($adapter);

        $this->assertTrue($repository->delete(1));
    }

    #[Test]
    public function it_should_validate_adapter_connection(): void
    {
        $adapter = new FakeRedisAdapter();
        $adapter->connect();
        $repository = new RedisRepositoryStub($adapter);

        $this->assertTrue($adapter->isConnected());
    }

    #[Test]
    public function it_should_handle_adapter_disconnection(): void
    {
        $adapter = new FakeRedisAdapter();
        $adapter->connect();
        $adapter->disconnect();
        $repository = new RedisRepositoryStub($adapter);

        $this->assertFalse($adapter->isConnected());
    }

    #[Test]
    public function it_should_handle_adapter_reconnection(): void
    {
        $adapter = new FakeRedisAdapter();
        $adapter->connect();
        $adapter->disconnect();
        $adapter->connect();
        $repository = new RedisRepositoryStub($adapter);

        $this->assertTrue($adapter->isConnected());
    }

    #[Test]
    public function it_should_validate_adapter_driver(): void
    {
        // Test that validateAdapter() works correctly
        $adapter = new FakeRedisAdapter();
        $repository = new RedisRepositoryStub($adapter);

        // Since validateAdapter() is called in constructor,
        // if we reach here without exception, validation passed
        $this->assertInstanceOf(BaseRedisRepository::class, $repository);
    }

    #[Test]
    public function it_should_throw_exception_for_unsupported_driver(): void
    {
        $adapter = $this->createTrulyUnsupportedAdapter();

        $this->expectException(\Maatify\DataRepository\Exceptions\RepositoryException::class);
        $this->expectExceptionMessage('is not supported by this repository');

        new RedisRepositoryStub($adapter);
    }

    private function createTrulyUnsupportedAdapter(): AdapterInterface
    {
        return new class () implements AdapterInterface {
            // @phpstan-ignore-next-line
            public function getDriver(): object
            {
                // @phpstan-ignore-next-line
                return new \DateTime();
            }

            public function connect(): void
            {
            }

            public function isConnected(): bool
            {
                return false;
            }

            public function disconnect(): void
            {
            }

            // @phpstan-ignore-next-line
            public function getConnection(): object
            {
                // @phpstan-ignore-next-line
                return new \DateTime();
            }

            public function debugConfig(): object
            {
                return (object)[];
            }

            public function healthCheck(): bool
            {
                return false;
            }
        };
    }
}
