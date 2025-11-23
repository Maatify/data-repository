<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Exceptions;

use RuntimeException;

class RepositoryException extends RuntimeException
{
    public static function forInvalidAdapter(string $expected, object $actual): self
    {
        $actualClass = $actual::class;
        $message = sprintf('Invalid adapter provided. Expected instance of %s, got %s.', $expected, $actualClass);

        return new self($message);
    }

    public static function forMissingDriver(string $driverDescription): self
    {
        $message = sprintf('Missing %s driver. Ensure the adapter exposes the requested driver.', $driverDescription);

        return new self($message);
    }

    public static function forInvalidDriver(string $expected, object $actual): self
    {
        $actualClass = $actual::class;
        $message = sprintf('Invalid driver instance. Expected %s, got %s.', $expected, $actualClass);

        return new self($message);
    }
}
