<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 13:10:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Hydration;

use DateTimeImmutable;
use Maatify\DataRepository\Hydration\AutoCaster;
use PHPUnit\Framework\TestCase;

class AutoCasterCoverageTest extends TestCase
{
    public function testCastNullValue(): void
    {
        $data = ['id' => null];
        $defs = ['id' => AutoCaster::TYPE_INT];
        $result = AutoCaster::cast($data, $defs);

        $this->assertNull($result['id']);
    }

    public function testCastNonScalarToScalarType(): void
    {
        $data = ['meta' => ['key' => 'value']];
        // Trying to cast an array to INT should fail gracefully and return the array as is (per code logic)
        $defs = ['meta' => AutoCaster::TYPE_INT];
        $result = AutoCaster::cast($data, $defs);

        $this->assertSame(['key' => 'value'], $result['meta']);
    }

    public function testCastDateTimeInstance(): void
    {
        $dt = new DateTimeImmutable('2024-01-01');
        $data = ['created_at' => $dt];
        $defs = ['created_at' => AutoCaster::TYPE_DATETIME];
        $result = AutoCaster::cast($data, $defs);

        $this->assertSame($dt, $result['created_at']);
    }

    public function testCastDateTimeInvalidString(): void
    {
        $data = ['created_at' => 'not-a-date'];
        $defs = ['created_at' => AutoCaster::TYPE_DATETIME];
        $result = AutoCaster::cast($data, $defs);

        $this->assertNull($result['created_at']);
    }

    public function testCastDateTimeInvalidType(): void
    {
        $data = ['created_at' => 123.45]; // float
        $defs = ['created_at' => AutoCaster::TYPE_DATETIME];
        $result = AutoCaster::cast($data, $defs);

        $this->assertNull($result['created_at']);
    }
}
