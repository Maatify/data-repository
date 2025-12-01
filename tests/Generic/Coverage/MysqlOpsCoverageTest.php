<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 02:35
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Coverage;

use Maatify\DataRepository\Generic\Support\MysqlOps;
use PHPUnit\Framework\TestCase;

class MysqlOpsCoverageTest extends TestCase
{
    public function testGetDriver(): void
    {
        $driver = new \stdClass();
        $ops = new MysqlOps($driver);
        $this->assertSame($driver, $ops->getDriver());
    }

    public function testLastInsertIdWithPdo(): void
    {
        $pdo = $this->createMock(\PDO::class);
        $ops = new MysqlOps($pdo);

        // Success int
        $pdo->expects($this->exactly(3))
            ->method('lastInsertId')
            ->willReturnOnConsecutiveCalls('123', false, 'abc');

        // 1. Numeric string -> int
        $this->assertSame(123, $ops->lastInsertId());

        // 2. False -> 0
        $this->assertSame(0, $ops->lastInsertId());

        // 3. String -> String
        $this->assertSame('abc', $ops->lastInsertId());
    }

    public function testLastInsertIdWithFakeDriver(): void
    {
        $fake = new class {
            public mixed $returnVal;
            public function lastInsertId(): mixed { return $this->returnVal; }
        };

        $ops = new MysqlOps($fake);

        // 1. Int
        $fake->returnVal = 456;
        $this->assertSame(456, $ops->lastInsertId());

        // 2. String
        $fake->returnVal = 'def';
        $this->assertSame('def', $ops->lastInsertId());

        // 3. False
        $fake->returnVal = false;
        $this->assertSame(0, $ops->lastInsertId());
    }

    public function testLastInsertIdWithUnsupportedDriver(): void
    {
        $driver = new \stdClass(); // No lastInsertId method
        $ops = new MysqlOps($driver);
        $this->assertSame(0, $ops->lastInsertId());
    }
}
