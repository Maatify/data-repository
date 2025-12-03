<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Architecture;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\Common\Pagination\DTO\PaginationDTO;
use Maatify\DataRepository\Base\BaseRepository;
use Maatify\DataRepository\Logging\RepositoryLogger;
use Maatify\Common\Pagination\DTO\PaginationResultDTO;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use PDO;

class LoggerInjectionTest extends TestCase
{
    private AdapterInterface $adapter;

    protected function setUp(): void
    {
        // Use a more concrete fake adapter to satisfy PHPStan
        $this->adapter = new class () implements AdapterInterface {
            public function connect(): void
            {
            }
            public function isConnected(): bool
            {
                return true;
            }
            /** @return PDO */
            public function getConnection(): mixed
            {
                return new PDO('sqlite::memory:');
            }
            /** @return PDO */
            public function getDriver(): mixed
            {
                return new PDO('sqlite::memory:');
            }
            public function disconnect(): void
            {
            }
            public function healthCheck(): bool
            {
                return true;
            }
            public function getType(): string
            {
                return 'fake';
            }
            public function debugConfig(): object
            {
                return (object)[];
            }
        };
    }

    public function testDefaultConstructorInjectsNullLogger(): void
    {
        $repo = new class ($this->adapter) extends BaseRepository {
            public function getLogger(): LoggerInterface
            {
                return $this->logger;
            }

            public function find(int|string $id): ?array
            {
                return null;
            }

            /**
             * @param array<string, mixed> $filters
             * @param array<string, string>|null $orderBy
             * @return array<int, array<string, mixed>>
             */
            public function findBy(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
            {
                return [];
            }

            /**
             * @param array<string, mixed> $filters
             * @return array<string, mixed>|null
             */
            public function findOneBy(array $filters): ?array
            {
                return rand(0, 1) ? [] : null;
            }

            /** @return array<int, array<string, mixed>> */
            public function findAll(): array
            {
                return [];
            }

            /** @param array<string, mixed> $filters */
            public function count(array $filters = []): int
            {
                return 0;
            }

            /** @param array<string, mixed> $data */
            public function insert(array $data): int|string
            {
                return rand(0, 1) ? 1 : '1';
            }

            /** @param array<string, mixed> $data */
            public function update(int|string $id, array $data): bool
            {
                return false;
            }

            public function delete(int|string $id): bool
            {
                return false;
            }

            /**
             * @param array<string, string>|null $orderBy
             */
            public function paginate(int $page = 1, int $perPage = 10, ?array $orderBy = null): PaginationResultDTO
            {
                return new PaginationResultDTO([], new PaginationDTO(1, 1, 0, 1, false, false));
            }

            /**
             * @param array<string, mixed> $filters
             * @param array<string, string>|null $orderBy
             */
            public function paginateBy(array $filters, int $page = 1, int $perPage = 10, ?array $orderBy = null): PaginationResultDTO
            {
                return new PaginationResultDTO([], new PaginationDTO(1, 1, 0, 1, false, false));
            }
        };

        $this->assertInstanceOf(NullLogger::class, $repo->getLogger());
    }

    public function testInjectsPsrLoggerDirectlyWithoutWrapper(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);

        $repo = new class ($this->adapter, $mockLogger) extends BaseRepository {
            public function getLogger(): LoggerInterface
            {
                return $this->logger;
            }
            public function find(int|string $id): ?array
            {
                return null;
            }

            /**
             * @param array<string, mixed> $filters
             * @param array<string, string>|null $orderBy
             * @return array<int, array<string, mixed>>
             */
            public function findBy(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
            {
                return [];
            }

            /**
             * @param array<string, mixed> $filters
             * @return array<string, mixed>|null
             */
            public function findOneBy(array $filters): ?array
            {
                return rand(0, 1) ? [] : null;
            }

            /** @return array<int, array<string, mixed>> */
            public function findAll(): array
            {
                return [];
            }

            /** @param array<string, mixed> $filters */
            public function count(array $filters = []): int
            {
                return 0;
            }

            /** @param array<string, mixed> $data */
            public function insert(array $data): int|string
            {
                return rand(0, 1) ? 1 : '1';
            }

            /** @param array<string, mixed> $data */
            public function update(int|string $id, array $data): bool
            {
                return false;
            }

            public function delete(int|string $id): bool
            {
                return false;
            }

            /**
             * @param array<string, string>|null $orderBy
             */
            public function paginate(int $page = 1, int $perPage = 10, ?array $orderBy = null): PaginationResultDTO
            {
                return new PaginationResultDTO([], new PaginationDTO(1, 1, 0, 1, false, false));
            }

            /**
             * @param array<string, mixed> $filters
             * @param array<string, string>|null $orderBy
             */
            public function paginateBy(array $filters, int $page = 1, int $perPage = 10, ?array $orderBy = null): PaginationResultDTO
            {
                return new PaginationResultDTO([], new PaginationDTO(1, 1, 0, 1, false, false));
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

        $repo = new class ($this->adapter, $wrapper) extends BaseRepository {
            public function getLogger(): LoggerInterface
            {
                return $this->logger;
            }
            public function find(int|string $id): ?array
            {
                return null;
            }

            /**
             * @param array<string, mixed> $filters
             * @param array<string, string>|null $orderBy
             * @return array<int, array<string, mixed>>
             */
            public function findBy(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
            {
                return [];
            }

            /**
             * @param array<string, mixed> $filters
             * @return array<string, mixed>|null
             */
            public function findOneBy(array $filters): ?array
            {
                return rand(0, 1) ? [] : null;
            }

            /** @return array<int, array<string, mixed>> */
            public function findAll(): array
            {
                return [];
            }

            /** @param array<string, mixed> $filters */
            public function count(array $filters = []): int
            {
                return 0;
            }

            /** @param array<string, mixed> $data */
            public function insert(array $data): int|string
            {
                return rand(0, 1) ? 1 : '1';
            }

            /** @param array<string, mixed> $data */
            public function update(int|string $id, array $data): bool
            {
                return false;
            }

            public function delete(int|string $id): bool
            {
                return false;
            }

            /**
             * @param array<string, string>|null $orderBy
             */
            public function paginate(int $page = 1, int $perPage = 10, ?array $orderBy = null): PaginationResultDTO
            {
                return new PaginationResultDTO([], new PaginationDTO(1, 1, 0, 1, false, false));
            }

            /**
             * @param array<string, mixed> $filters
             * @param array<string, string>|null $orderBy
             */
            public function paginateBy(array $filters, int $page = 1, int $perPage = 10, ?array $orderBy = null): PaginationResultDTO
            {
                return new PaginationResultDTO([], new PaginationDTO(1, 1, 0, 1, false, false));
            }
        };

        $injectedLogger = $repo->getLogger();
        $this->assertSame($wrapper, $injectedLogger);
        $this->assertInstanceOf(RepositoryLogger::class, $injectedLogger);
    }
}
