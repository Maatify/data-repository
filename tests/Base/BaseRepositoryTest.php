<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Base;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Base\BaseRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;

class BaseRepositoryTest extends TestCase
{
    public function testSetAdapterReturnsSelfAndValidates(): void
    {
        $adapter = new RecordingAdapter(new PDO('sqlite::memory:'));

        $repository = new class ($adapter) extends BaseRepository {
            public bool $validated = false;

            protected function validateAdapter(): void
            {
                $this->validated = true;
            }

            public function find(int|string $id): ?array
            {
                return null;
            }

            /**
             * @param   array<string, mixed>        $filters
             * @param   array<string, string>|null  $orderBy
             *
             * @return array<int, array<string, mixed>>
             */
            public function findBy(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
            {
                return [];
            }

            /**
             * Return both branches so PHPStan doesn't mark parts of the union as unused.
             *
             * @param   array<string, mixed>  $filters
             *
             * @return array<string, mixed>|null
             */
            public function findOneBy(array $filters): ?array
            {
                if ($filters === []) {
                    return null;
                }

                return ['ok' => true];
            }

            /**
             * @return array<int, array<string, mixed>>
             */
            public function findAll(): array
            {
                return [];
            }

            /**
             * @param   array<string, mixed>  $filters
             */
            public function count(array $filters = []): int
            {
                return 0;
            }

            /**
             * Return both int and string branches so PHPStan doesn't mark parts of the union as unused.
             *
             * @param   array<string, mixed>  $data
             *
             * @return int|string
             */
            public function insert(array $data): int|string
            {
                if ($data === []) {
                    return '1';
                }

                return 1;
            }

            /**
             * @param   array<string, mixed>  $data
             */
            public function update(int|string $id, array $data): bool
            {
                return false;
            }

            public function delete(int|string $id): bool
            {
                return false;
            }
        };

        $result = $repository->setAdapter($adapter);

        $this->assertSame($repository, $result);
        $this->assertTrue($repository->validated);
    }

    public function testConstructorInjectsLoggerDirectlyWithoutWrapper(): void
    {
        $adapter = new RecordingAdapter(new PDO('sqlite::memory:'));
        $logger = new InMemoryLogger();

        $repository = new class ($adapter, $logger) extends BaseRepository {
            /**
             * @param array<string, mixed> $context
             */
            public function triggerLog(string|Stringable $message, array $context = []): void
            {
                $this->logger->info($message, $context);
            }

            public function find(int|string $id): ?array
            {
                return null;
            }

            /**
             * @param   array<string, mixed>        $filters
             * @param   array<string, string>|null  $orderBy
             *
             * @return array<int, array<string, mixed>>
             */
            public function findBy(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
            {
                return [];
            }

            /**
             * @param   array<string, mixed>  $filters
             *
             * @return array<string, mixed>|null
             */
            public function findOneBy(array $filters): ?array
            {
                if ($filters === []) {
                    return null;
                }

                return ['ok' => true];
            }

            /**
             * @return array<int, array<string, mixed>>
             */
            public function findAll(): array
            {
                return [];
            }

            /**
             * @param   array<string, mixed>  $filters
             */
            public function count(array $filters = []): int
            {
                return 0;
            }

            /**
             * @param   array<string, mixed>  $data
             *
             * @return int|string
             */
            public function insert(array $data): int|string
            {
                if ($data === []) {
                    return '1';
                }

                return 1;
            }

            /**
             * @param   array<string, mixed>  $data
             */
            public function update(int|string $id, array $data): bool
            {
                return true;
            }

            public function delete(int|string $id): bool
            {
                return true;
            }
        };

        $repository->triggerLog('hello-world', ['foo' => 'bar']);

        $this->assertCount(1, $logger->records);
        $this->assertSame('info', $logger->records[0]['level']);
        $this->assertSame('hello-world', $logger->records[0]['message']);
        $this->assertSame(
            [
                'foo' => 'bar',
            ],
            $logger->records[0]['context']
        );
    }

    public function testTableNameAndDriverAccessors(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $adapter = new RecordingAdapter($pdo);

        $repository = new class ($adapter) extends BaseRepository {
            public function rename(string $new): void
            {
                $this->setTableName($new);
            }

            public function driver(): mixed
            {
                return $this->getDriver();
            }

            public function find(int|string $id): ?array
            {
                return null;
            }

            /**
             * @param   array<string, mixed>        $filters
             * @param   array<string, string>|null  $orderBy
             *
             * @return array<int, array<string, mixed>>
             */
            public function findBy(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
            {
                return [];
            }

            /**
             * @param   array<string, mixed>  $filters
             *
             * @phpstan-ignore-next-line return type narrowed only for testing stub
             */
            public function findOneBy(array $filters): ?array
            {
                return null;
            }

            /**
             * @return array<int, array<string, mixed>>
             */
            public function findAll(): array
            {
                return [];
            }

            /**
             * @param   array<string, mixed>  $filters
             */
            public function count(array $filters = []): int
            {
                return 0;
            }

            /**
             * @param   array<string, mixed>  $data
             *
             * @phpstan-ignore-next-line return type narrowed only for testing stub
             */
            public function insert(array $data): int|string
            {
                return 1;
            }

            /**
             * @param   array<string, mixed>  $data
             */
            public function update(int|string $id, array $data): bool
            {
                return true;
            }

            public function delete(int|string $id): bool
            {
                return true;
            }
        };

        $repository->rename('custom_table');

        $this->assertSame('custom_table', $repository->getTableName());
        $this->assertSame($pdo, $repository->driver());
    }
}

class RecordingAdapter implements AdapterInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getDriver(): PDO
    {
        return $this->pdo;
    }

    public function getType(): string
    {
        return 'recording';
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

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function debugConfig(): object
    {
        return (object) ['driver' => $this->pdo::class];
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

    /**
     * @param array<string, mixed> $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'level' => $level,
            'message' => (string) $message,
            'context' => $context,
        ];
    }
}
