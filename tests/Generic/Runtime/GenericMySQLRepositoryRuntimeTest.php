<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-26 06:56
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Runtime;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use PDO;
use PHPUnit\Framework\TestCase;

interface NativePdoProviderInterface
{
    public function getNativeConnection(): PDO;
}

class GenericMySQLRepositoryRuntimeTest extends TestCase
{
    public function testGetPdoFallbackToNativeConnection(): void
    {
        $pdo = new PDO('sqlite::memory:');

        $driver = new class ($pdo) implements NativePdoProviderInterface {
            public function __construct(private PDO $pdo)
            {
            }

            public function getNativeConnection(): PDO
            {
                return $this->pdo;
            }
        };

        // Adapter returns a REAL PDO → type-safe
        $adapter = new class ($driver) implements AdapterInterface {
            public function __construct(private NativePdoProviderInterface $driver)
            {
            }

            public function getDriver(): PDO
            {
                return $this->driver->getNativeConnection();
            }

            public function getConnection(): PDO
            {
                return $this->driver->getNativeConnection();
            }

            public function getType(): string
            {
                return 'mysql';
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
                return (object) [];
            }
            public function healthCheck(): bool
            {
                return true;
            }
        };

        $repo = new class ($adapter) extends GenericMySQLRepository {
            protected string $tableName = 'fallback_test';

            public function __construct(AdapterInterface $adapter)
            {
                $this->adapter = $adapter;
            }

            public function expose(): PDO
            {
                $ref = new \ReflectionMethod(GenericMySQLRepository::class, 'getPdo');
                $ref->setAccessible(true);

                $result = $ref->invoke($this);

                if (!$result instanceof PDO) {
                    throw new \RuntimeException('Expected PDO from getPdo().');
                }

                return $result;
            }
        };

        $pdoResult = $repo->expose();
        $this->assertInstanceOf(PDO::class, $pdoResult);
    }

    public function testRejectsNonCompatibleDriver(): void
    {
        // Use a valid return type (Redis) but incompatible for MySQL repositories
        $adapter = new class () implements AdapterInterface {
            public function getDriver(): \Redis
            {
                return new \Redis(); // valid type, but NOT PDO → will trigger RepositoryException
            }

            public function getConnection(): \Redis
            {
                return new \Redis();
            }

            public function getType(): string
            {
                return 'mysql';
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
                return (object) [];
            }
            public function healthCheck(): bool
            {
                return true;
            }
        };

        $repo = new class ($adapter) extends GenericMySQLRepository {
            protected string $tableName = 'invalid_test';

            public function __construct(AdapterInterface $adapter)
            {
                $this->adapter = $adapter;
            }

            public function trigger(): void
            {
                $ref = new \ReflectionMethod(GenericMySQLRepository::class, 'getPdo');
                $ref->setAccessible(true);
                $ref->invoke($this); // should throw RepositoryException BEFORE any SQL
            }
        };

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage(
            'GenericMySQLRepository requires a PDO driver or compatible wrapper.'
        );

        $repo->trigger();
    }
}
