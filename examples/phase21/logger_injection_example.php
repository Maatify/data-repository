<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-02 02:00:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Examples\Phase21;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\Common\Pagination\DTO\PaginationResultDTO;
use Maatify\DataRepository\Base\BaseRepository;
use Maatify\DataRepository\Logging\RepositoryLogger;
use Psr\Log\LoggerInterface;

// Mock dependencies for the example
class MockAdapter implements AdapterInterface {
    public function connect(): void {}
    public function isConnected(): bool { return true; }
    public function getConnection(): mixed { return null; }
    public function getDriver(): mixed { return new \stdClass(); }
    public function disconnect(): void {}
    public function healthCheck(): bool { return true; }
    public function getType(): string { return 'mock'; }
    public function debugConfig(): object { return (object)[]; }
}

class UserRepository extends BaseRepository {
    public function find(int|string $id): ?array { return null; }
    public function findBy(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $filters): ?array { return null; }
    public function findAll(): array { return []; }
    public function count(array $filters = []): int { return 0; }
    public function insert(array $data): int|string { return 1; }
    public function update(int|string $id, array $data): bool { return true; }
    public function delete(int|string $id): bool { return true; }
    public function paginate(int $page = 1, int $perPage = 10, ?array $orderBy = null): PaginationResultDTO {
        // Return dummy object for example
        /** @var PaginationResultDTO $mock */
        $mock = new \stdClass();
        return $mock;
    }
    public function paginateBy(array $filters, int $page = 1, int $perPage = 10, ?array $orderBy = null): PaginationResultDTO {
        // Return dummy object for example
        /** @var PaginationResultDTO $mock */
        $mock = new \stdClass();
        return $mock;
    }
}

class AppLogger implements LoggerInterface {
    public function emergency(string|\Stringable $message, array $context = []): void { echo "[EMERGENCY] $message" . PHP_EOL; }
    public function alert(string|\Stringable $message, array $context = []): void { echo "[ALERT] $message" . PHP_EOL; }
    public function critical(string|\Stringable $message, array $context = []): void { echo "[CRITICAL] $message" . PHP_EOL; }
    public function error(string|\Stringable $message, array $context = []): void { echo "[ERROR] $message" . PHP_EOL; }
    public function warning(string|\Stringable $message, array $context = []): void { echo "[WARNING] $message" . PHP_EOL; }
    public function notice(string|\Stringable $message, array $context = []): void { echo "[NOTICE] $message" . PHP_EOL; }
    public function info(string|\Stringable $message, array $context = []): void { echo "[INFO] $message" . PHP_EOL; }
    public function debug(string|\Stringable $message, array $context = []): void { echo "[DEBUG] $message" . PHP_EOL; }
    public function log($level, string|\Stringable $message, array $context = []): void { echo "[$level] $message" . PHP_EOL; }
}

$adapter = new MockAdapter();
$appLogger = new AppLogger();

// 1. Default Behavior (No Logger) -> NullLogger
$repo1 = new UserRepository($adapter);
// $repo1->logger is NullLogger

// 2. Raw Injection (New Behavior in Phase 21)
// Logs will be passed directly to AppLogger without 'source' context modification
$repo2 = new UserRepository($adapter, $appLogger);
// $repo2->logger is AppLogger

// 3. Manual Wrapping (Restoring Old Behavior)
// Logs will have 'source' => 'maatify/data-repository' context added
$wrappedLogger = new RepositoryLogger($appLogger);
$repo3 = new UserRepository($adapter, $wrappedLogger);
// $repo3->logger is RepositoryLogger

echo "Example executed successfully." . PHP_EOL;
