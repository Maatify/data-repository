<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Support;

use Maatify\DataRepository\Generic\Support\MysqlOps;
use PDO;
use PHPUnit\Framework\TestCase;

class MysqlOpsTest extends TestCase
{
    public function testGetDriver(): void
    {
        $driver = new \stdClass();
        $ops = new MysqlOps($driver);
        $this->assertSame($driver, $ops->getDriver());
    }

    public function testLastInsertIdWithPDO(): void
    {
        if (!class_exists(PDO::class)) {
            $this->markTestSkipped('PDO class not found');
        }

        $pdo = $this->createMock(PDO::class);
        $pdo->method('lastInsertId')->willReturn('123');

        $ops = new MysqlOps($pdo);
        $this->assertSame(123, $ops->lastInsertId());
    }

    public function testLastInsertIdWithPDOFalse(): void
    {
        if (!class_exists(PDO::class)) {
            $this->markTestSkipped('PDO class not found');
        }

        $pdo = $this->createMock(PDO::class);
        $pdo->method('lastInsertId')->willReturn(false);

        $ops = new MysqlOps($pdo);
        $this->assertSame(0, $ops->lastInsertId());
    }

    public function testLastInsertIdWithPDOLargeInt(): void
    {
        if (!class_exists(PDO::class)) {
            $this->markTestSkipped('PDO class not found');
        }

        $pdo = $this->createMock(PDO::class);
        // A string that fits in 64-bit int but is passed as string
        $pdo->method('lastInsertId')->willReturn((string)PHP_INT_MAX);

        $ops = new MysqlOps($pdo);
        $this->assertSame(PHP_INT_MAX, $ops->lastInsertId());
    }

    public function testLastInsertIdWithPDOOverflowString(): void
    {
        if (!class_exists(PDO::class)) {
            $this->markTestSkipped('PDO class not found');
        }

        $pdo = $this->createMock(PDO::class);
        // A string larger than PHP_INT_MAX (as string)
        $largeString = '999999999999999999999999999999';
        $pdo->method('lastInsertId')->willReturn($largeString);

        $ops = new MysqlOps($pdo);
        $this->assertSame($largeString, $ops->lastInsertId());
    }

    public function testLastInsertIdWithNonPDODriver(): void
    {
        $driver = new class {
            public function lastInsertId(): int
            {
                return 456;
            }
        };

        $ops = new MysqlOps($driver);
        $this->assertSame(456, $ops->lastInsertId());
    }

    public function testLastInsertIdWithNonPDODriverString(): void
    {
        $driver = new class {
            public function lastInsertId(): string
            {
                return '789';
            }
        };

        $ops = new MysqlOps($driver);
        $this->assertSame(789, $ops->lastInsertId());
    }

    public function testLastInsertIdWithNonPDODriverOverflowString(): void
    {
        $largeString = '999999999999999999999999999999';
        $driver = new class ($largeString) {
            private string $id;
            public function __construct(string $id)
            {
                $this->id = $id;
            }
            public function lastInsertId(): string
            {
                return $this->id;
            }
        };

        $ops = new MysqlOps($driver);
        $this->assertSame($largeString, $ops->lastInsertId());
    }

    public function testLastInsertIdWithNonPDODriverFalse(): void
    {
        $driver = new class {
            public function lastInsertId(): bool
            {
                return false;
            }
        };

        $ops = new MysqlOps($driver);
        $this->assertSame(0, $ops->lastInsertId());
    }

    public function testLastInsertIdWithNonPDODriverMissingMethod(): void
    {
        $driver = new \stdClass();
        $ops = new MysqlOps($driver);
        $this->assertSame(0, $ops->lastInsertId());
    }
}
