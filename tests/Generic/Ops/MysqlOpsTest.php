<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 03:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Ops;

use Maatify\DataRepository\Generic\Support\MysqlOps;
use PHPUnit\Framework\TestCase;

class MysqlOpsTest extends TestCase
{
    public function testGetDriver(): void
    {
        $pdo = $this->createMock(\PDO::class);
        $ops = new MysqlOps($pdo);

        $this->assertSame($pdo, $ops->getDriver());
    }

    public function testLastInsertIdReturnsZeroOnFalse(): void
    {
        $pdo = $this->createMock(\PDO::class);
        $pdo->method('lastInsertId')->willReturn(false);

        $ops = new MysqlOps($pdo);
        $this->assertSame(0, $ops->lastInsertId());
    }

    public function testLastInsertIdReturnsNumeric(): void
    {
        $pdo = $this->createMock(\PDO::class);
        // PDO typically returns string for numeric IDs
        $pdo->method('lastInsertId')->willReturn('123');

        $ops = new MysqlOps($pdo);
        $this->assertSame(123, $ops->lastInsertId());
    }

    public function testLastInsertIdReturnsInt(): void
    {
        // Simulate a driver that returns int
        $mockDriver = new class () {
            public function lastInsertId(): int
            {
                return 456;
            }
        };

        $ops = new MysqlOps($mockDriver);
        $this->assertSame(456, $ops->lastInsertId());
    }
}
