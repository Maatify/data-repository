<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Architecture;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Base\BaseRepository;
use Maatify\DataRepository\Logging\RepositoryLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LoggerInjectionTest extends TestCase
{
    private AdapterInterface $adapter;

    protected function setUp(): void
    {
        $this->adapter = new class implements AdapterInterface {
            public function connect(): void {}
            public function isConnected(): bool { return true; }
            public function getConnection(): mixed { return null; }
            public function getDriver(): mixed { return new \stdClass(); }
            public function disconnect(): void {}
            public function healthCheck(): bool { return true; }
            public function getType(): string { return 'fake'; }
        };
    }

    public function testDefaultConstructorInjectsNullLogger(): void
    {
        $repo = new class($this->adapter) extends BaseRepository {
            public function getLogger(): LoggerInterface
            {
                return $this->logger;
            }
        };

        $this->assertInstanceOf(NullLogger::class, $repo->getLogger());
    }

    public function testInjectsPsrLoggerDirectlyWithoutWrapper(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);

        $repo = new class($this->adapter, $mockLogger) extends BaseRepository {
            public function getLogger(): LoggerInterface
            {
                return $this->logger;
            }
        };

        $injectedLogger = $repo->getLogger();
        $this->assertSame($mockLogger, $injectedLogger);
        $this->assertNotInstanceOf(RepositoryLogger::class, $injectedLogger);
    }

    public function testManualRepositoryLoggerWrappingAllowed(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $wrapper = new RepositoryLogger($mockLogger);

        $repo = new class($this->adapter, $wrapper) extends BaseRepository {
            public function getLogger(): LoggerInterface
            {
                return $this->logger;
            }
        };

        $injectedLogger = $repo->getLogger();
        $this->assertSame($wrapper, $injectedLogger);
        $this->assertInstanceOf(RepositoryLogger::class, $injectedLogger);
    }
}
