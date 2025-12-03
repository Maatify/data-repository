<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Ordering;

use Maatify\DataRepository\Generic\Support\MongoOrderBuilder;
use Maatify\DataRepository\Generic\Support\OrderUtils;
use PHPUnit\Framework\TestCase;

class MongoOrderBuilderTest extends TestCase
{
    private MongoOrderBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new MongoOrderBuilder();
    }

    public function testBuildEmptyReturnsEmptyArray(): void
    {
        $this->assertSame([], $this->builder->build([]));
        $this->assertSame([], $this->builder->build(null));
    }

    public function testBuildSingleColumnAsc(): void
    {
        $expected = ['name' => 1];
        $this->assertSame($expected, $this->builder->build(['name' => 'ASC']));
    }

    public function testBuildSingleColumnDesc(): void
    {
        $expected = ['age' => -1];
        $this->assertSame($expected, $this->builder->build(['age' => 'DESC']));
    }

    public function testBuildMultipleColumns(): void
    {
        $orderBy = ['name' => 'ASC', 'created_at' => 'DESC'];
        $expected = ['name' => 1, 'created_at' => -1];
        $this->assertSame($expected, $this->builder->build($orderBy));
    }

    public function testBuildNormalizesDirections(): void
    {
        // 'asc' -> 1, 'desc' -> -1 (case insensitive handling in OrderParser/Utils)
        $orderBy = ['name' => 'asc', 'age' => 'desc'];
        $expected = ['name' => 1, 'age' => -1];
        $this->assertSame($expected, $this->builder->build($orderBy));
    }

    public function testBuildInvalidDirectionDefaultsToAsc(): void
    {
        $orderBy = ['name' => 'INVALID'];
        $expected = ['name' => 1];
        $this->assertSame($expected, $this->builder->build($orderBy));
    }
}
