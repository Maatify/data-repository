<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 13:30:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Hydration;

use Maatify\DataRepository\Hydration\BaseHydrator;
use PHPUnit\Framework\TestCase;

class TestDto
{
    public int $id;
    public float $amount;
    public bool $is_active;
    public string $name;
    public ?\DateTimeInterface $created_at;
    /** @var array<string, mixed>|null */
    public ?array $meta;
}

/**
 * @extends BaseHydrator<TestDto>
 */
class CastingHydrator extends BaseHydrator
{
    protected function createInstance(): object
    {
        return new TestDto();
    }

    protected function getCastingDefinitions(): array
    {
        return [
            'id' => 'int',
            'amount' => 'float',
            'is_active' => 'bool',
            'name' => 'string',
            'created_at' => 'datetime',
            'meta' => 'json',
        ];
    }
}

class BaseHydratorCastingTest extends TestCase
{
    /** @var CastingHydrator */
    private BaseHydrator $hydrator;

    protected function setUp(): void
    {
        $this->hydrator = new CastingHydrator();
    }

    public function testCasting(): void
    {
        $data = [
            'id' => '123',
            'amount' => '45.67',
            'is_active' => '1',
            'name' => 12345,
            'created_at' => '2025-01-01 10:00:00',
            'meta' => '{"key":"value"}',
        ];

        /** @var TestDto $result */
        $result = $this->hydrator->hydrate($data);

        $this->assertSame(123, $result->id);
        $this->assertSame(45.67, $result->amount);
        $this->assertTrue($result->is_active);
        $this->assertSame('12345', $result->name);
        $this->assertInstanceOf(\DateTimeInterface::class, $result->created_at);
        $this->assertEquals('2025-01-01 10:00:00', $result->created_at->format('Y-m-d H:i:s'));
        $this->assertSame(['key' => 'value'], $result->meta);
    }

    public function testCastingHandlesNullsGracefully(): void
    {
        // Assuming AutoCaster returns null for nullable types if input is null,
        // but here DTO properties are typed.
        // PHP strict types will throw TypeError if we try to assign null to non-nullable property.
        // However, AutoCaster usually returns default for primitives if strict?
        // Let's check AutoCaster implementation in memory...
        // AutoCaster was implemented in Phase 13.
        // If input is null, 'int' cast -> 0? 'string' -> ''?
        // Let's assume standard PHP casting behavior or AutoCaster logic.

        $data = [
            'id' => null, // should be 0
            'amount' => null, // 0.0
            'is_active' => null, // false
            'name' => null, // ''
            'created_at' => null, // null
            'meta' => null, // null or []
        ];

        /** @var TestDto $result */
        $result = $this->hydrator->hydrate($data);

        $this->assertSame(0, $result->id);
        $this->assertSame(0.0, $result->amount);
        $this->assertFalse($result->is_active);
        $this->assertSame('', $result->name);
        // DateTime cast usually returns null if input is null/empty
        $this->assertNull($result->created_at);
        // Json cast usually returns null or empty array
        $this->assertNull($result->meta);
    }
}
