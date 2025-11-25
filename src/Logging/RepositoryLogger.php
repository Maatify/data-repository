<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-24 16:49:00
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Logging;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;

/**
 * Wrapper to ensure all repository logs carry the correct source context.
 * Uses standard Psr\Log interfaces provided by psr/log (via maatify/psr-logger).
 */
class RepositoryLogger implements LoggerInterface
{
    use LoggerTrait;

    public function __construct(private LoggerInterface $wrappedLogger)
    {
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        // Ensure source context is set for the repository layer
        $context['source'] = 'maatify/data-repository';
        $this->wrappedLogger->log($level, $message, $context);
    }
}
