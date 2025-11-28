<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 23:45
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Resolver;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Resolver\RepositoryResolver;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class RepositoryResolverTest extends TestCase
{
    /**
     * @throws RepositoryException
     */
    public function testRegisterAndRetrieveAdapter(): void
    {
        $adapter = new DummyAdapter();
        $logger = new InMemoryLogger();

        $resolver = new RepositoryResolver($logger);
        $resolver->registerAdapter('mysql', $adapter);

        $this->assertTrue($resolver->hasAdapter('mysql'));
        $this->assertSame($adapter, $resolver->getAdapter('mysql'));

        $this->assertCount(1, $logger->records);
        $this->assertSame('info', $logger->records[0]['level']);
        $this->assertSame('Adapter registered: mysql', $logger->records[0]['message']);
        $this->assertIsArray($logger->records[0]['context']);
        $this->assertSame('maatify/data-repository', $logger->records[0]['context']['source']);
    }

    public function testGetAdapterThrowsWhenMissing(): void
    {
        $resolver = new RepositoryResolver();

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Driver 'missing' is not supported by this repository.");

        $resolver->getAdapter('missing');
    }
}

class DummyAdapter implements AdapterInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = new \PDO('sqlite::memory:'); // خفيف وأمن للاختبار
    }

    public function getDriver(): \PDO
    {
        return $this->pdo; // متوافق مع return types
    }

    public function getConnection(): \PDO
    {
        return $this->pdo; // متوافق مع return types
    }

    public function getType(): string
    {
        return 'dummy';
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
    public function debugConfig(): object
    {
        return (object) ['driver' => 'dummy'];
    }
    public function healthCheck(): bool
    {
        return true;
    }
}

class InMemoryLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $records = [];

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }
}
