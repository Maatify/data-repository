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

namespace Maatify\DataRepository\Exceptions;

use Exception;
use Throwable;

class RepositoryException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function driverNotSupported(string $driver): self
    {
        return new self("Driver '{$driver}' is not supported by this repository.");
    }

    public static function connectionFailed(string $reason): self
    {
        return new self("Repository connection failed: {$reason}");
    }
}
