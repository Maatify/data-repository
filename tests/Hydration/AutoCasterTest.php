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

class AutoCasterTest extends TestCase
{
    public function testCastInt(): void
    {
        $data = ['id' => '123'];
        $defs = ['id' => AutoCaster::TYPE_INT];
        $result = AutoCaster::cast($data, $defs);

        $this->assertSame(123, $result['id']);
    }

    public function testCastFloat(): void
    {
        $data = ['price' => '12.50'];
        $defs = ['price' => AutoCaster::TYPE_FLOAT];
        $result = AutoCaster::cast($data, $defs);

        $this->assertSame(12.5, $result['price']);
    }

    public function testCastBool(): void
    {
        $data = ['active' => '1', 'pending' => 0];
        $defs = ['active' => AutoCaster::TYPE_BOOL, 'pending' => AutoCaster::TYPE_BOOL];
        $result = AutoCaster::cast($data, $defs);

        $this->assertTrue($result['active']);
        $this->assertFalse($result['pending']);
    }

    public function testCastDateTimeFromString(): void
    {
        $data = ['created_at' => '2023-01-01 10:00:00'];
        $defs = ['created_at' => AutoCaster::TYPE_DATETIME];
        $result = AutoCaster::cast($data, $defs);

        $this->assertInstanceOf(DateTimeImmutable::class, $result['created_at']);
        $this->assertEquals('2023-01-01 10:00:00', $result['created_at']->format('Y-m-d H:i:s'));
    }

    public function testCastDateTimeFromTimestamp(): void
    {
        $timestamp = 1672531200; // 2023-01-01 00:00:00 UTC
        $data = ['updated_at' => $timestamp];
        $defs = ['updated_at' => AutoCaster::TYPE_DATETIME];
        $result = AutoCaster::cast($data, $defs);

        $this->assertInstanceOf(DateTimeImmutable::class, $result['updated_at']);
        $this->assertEquals($timestamp, $result['updated_at']->getTimestamp());
    }

    public function testCastJsonFromString(): void
    {
        $json = '{"key":"value"}';
        $data = ['meta' => $json];
        $defs = ['meta' => AutoCaster::TYPE_JSON];
        $result = AutoCaster::cast($data, $defs);

        $this->assertIsArray($result['meta']);
        $this->assertSame('value', $result['meta']['key']);
    }

    public function testCastJsonAlreadyArray(): void
    {
        $data = ['meta' => ['key' => 'value']];
        $defs = ['meta' => AutoCaster::TYPE_JSON];
        $result = AutoCaster::cast($data, $defs);

        $this->assertIsArray($result['meta']);
        $this->assertSame('value', $result['meta']['key']);
    }

    public function testCastJsonInvalid(): void
    {
        $data = ['meta' => 'invalid-json'];
        $defs = ['meta' => AutoCaster::TYPE_JSON];
        $result = AutoCaster::cast($data, $defs);

        $this->assertSame([], $result['meta']);
    }

    public function testIgnoreMissingFields(): void
    {
        $data = ['name' => 'John'];
        $defs = ['age' => AutoCaster::TYPE_INT];
        $result = AutoCaster::cast($data, $defs);

        $this->assertArrayNotHasKey('age', $result);
        $this->assertSame('John', $result['name']);
    }
}
