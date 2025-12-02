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
use Maatify\DataRepository\Base\BaseRepository;
use Maatify\DataRepository\Logging\RepositoryLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

// Mock dependencies for the example
class MockAdapter implements AdapterInterface {
    public function connect(): void {}
    public function isConnected(): bool { return true; }
    public function getConnection(): mixed { return null; }
    public function getDriver(): mixed { return new \stdClass(); }
    public function disconnect(): void {}
    public function healthCheck(): bool { return true; }
    public function getType(): string { return 'mock'; }
}

class UserRepository extends BaseRepository {}

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
