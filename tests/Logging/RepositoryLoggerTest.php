<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-26 00:02
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Logging;

use Maatify\DataRepository\Logging\RepositoryLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;

class RepositoryLoggerTest extends TestCase
{
    public function testLogAddsSourceContext(): void
    {
        $inMemoryLogger = new class () implements LoggerInterface {
            use LoggerTrait;

            /**
             * @var array<int, array<string, mixed>>
             */
            public array $records = [];

            public function log($level, string|Stringable $message, array $context = []): void
            {
                $this->records[] = [
                    'level' => $level,
                    'message' => (string) $message,
                    'context' => $context,
                ];
            }
        };

        $logger = new RepositoryLogger($inMemoryLogger);
        $logger->info('test-message', ['foo' => 'bar']);

        $this->assertCount(1, $inMemoryLogger->records);
        $this->assertSame('info', $inMemoryLogger->records[0]['level']);
        $this->assertSame('test-message', $inMemoryLogger->records[0]['message']);
        $this->assertSame(
            [
                'foo' => 'bar',
                'source' => 'maatify/data-repository',
            ],
            $inMemoryLogger->records[0]['context']
        );
    }

    public function testLogOverridesProvidedSourceAndHandlesStringableMessage(): void
    {
        $inMemoryLogger = new class () implements LoggerInterface {
            use LoggerTrait;

            /**
             * @var array<int, array<string, mixed>>
             */
            public array $records = [];

            public function log($level, string|Stringable $message, array $context = []): void
            {
                $this->records[] = [
                    'level' => $level,
                    'message' => (string) $message,
                    'context' => $context,
                ];
            }
        };

        $logger = new RepositoryLogger($inMemoryLogger);
        $logger->notice(new class () implements Stringable {
            public function __toString(): string
            {
                return 'stringable-body';
            }
        }, ['source' => 'custom-source']);

        $this->assertSame('stringable-body', $inMemoryLogger->records[0]['message']);

        $this->assertIsArray($inMemoryLogger->records[0]['context']);
        $this->assertSame('maatify/data-repository', $inMemoryLogger->records[0]['context']['source']);

    }
}
