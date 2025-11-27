<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Exceptions;

use Maatify\DataRepository\Exceptions\RepositoryException;
use PHPUnit\Framework\TestCase;

class RepositoryExceptionTest extends TestCase
{
    public function testDriverNotSupportedMessage(): void
    {
        $exception = RepositoryException::driverNotSupported('UnknownDriver');

        $this->assertInstanceOf(RepositoryException::class, $exception);
        $this->assertStringContainsString('UnknownDriver', $exception->getMessage());
    }

    public function testConnectionFailedFactory(): void
    {
        $exception = RepositoryException::connectionFailed('Timeout');

        $this->assertSame('Repository connection failed: Timeout', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }
}
