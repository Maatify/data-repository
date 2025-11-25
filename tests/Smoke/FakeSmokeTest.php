<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 00:00:00
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Smoke;

use Maatify\DataFakes\Adapters\MySQL\FakeMySQLAdapter;
use Maatify\DataFakes\Storage\FakeStorageLayer;
use Maatify\DataRepository\Resolver\RepositoryResolver;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class FakeSmokeTest extends TestCase
{
    public function testResolverCanRegisterFakeAdapter(): void
    {
        // Use local test logger
        $logger = new TestInMemoryLogger();
        $resolver = new RepositoryResolver($logger);

        // Create dependencies for the fake adapter
        $storage = new FakeStorageLayer();
        $adapter = new FakeMySQLAdapter($storage);

        $resolver->registerAdapter('mysql_fake', $adapter);

        $this->assertTrue($resolver->hasAdapter('mysql_fake'));
        $this->assertSame($adapter, $resolver->getAdapter('mysql_fake'));

        // Verify logger
        $logs = $logger->getLogs();
        $this->assertNotEmpty($logs);
        // PHPStan now knows the shape of $logs[0]
        $this->assertSame('Adapter registered: mysql_fake', $logs[0]['message']);
    }
}

/**
 * Internal mock logger for testing.
 */
class TestInMemoryLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var array<int, array{level: mixed, message: string, context: array<mixed>}>
     */
    private array $logs = [];

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => (string)$message,
            'context' => $context,
        ];
    }

    /**
     * @return array<int, array{level: mixed, message: string, context: array<mixed>}>
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
