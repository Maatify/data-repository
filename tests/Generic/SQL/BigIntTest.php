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

namespace Maatify\DataRepository\Tests\Generic\SQL;

use Maatify\DataRepository\Generic\Support\MysqlOps;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Validates handling of large integers in MysqlOps.
 */
class BigIntTest extends TestCase
{
    public function testSmallIntIsCastToInt(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->method('lastInsertId')->willReturn('123');

        $ops = new MysqlOps($pdo);
        $result = $ops->lastInsertId();

        $this->assertIsInt($result);
        $this->assertEquals(123, $result);
    }

    public function testBigIntStringPreservedAsString(): void
    {
        // 64-bit unsigned max is ~1.8e19. We use a number guaranteed to be huge.
        $huge = '999999999999999999999999999999';

        $pdo = $this->createMock(PDO::class);
        $pdo->method('lastInsertId')->willReturn($huge);

        $ops = new MysqlOps($pdo);
        $result = $ops->lastInsertId();

        $this->assertIsString($result);
        $this->assertEquals($huge, $result);
    }

    public function testBorderlineIntSafelyCast(): void
    {
        // PHP_INT_MAX
        $max = (string)PHP_INT_MAX;

        $pdo = $this->createMock(PDO::class);
        $pdo->method('lastInsertId')->willReturn($max);

        $ops = new MysqlOps($pdo);
        $result = $ops->lastInsertId();

        $this->assertIsInt($result);
        $this->assertEquals(PHP_INT_MAX, $result);
    }
}
