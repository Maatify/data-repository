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

namespace Maatify\DataRepository\Tests\Generic\Filters;

use InvalidArgumentException;
use Maatify\DataRepository\Generic\Support\MySQLFilterBuilder;
use PHPUnit\Framework\TestCase;

class MySQLFilterBuilderTest extends TestCase
{
    private MySQLFilterBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new MySQLFilterBuilder();
    }

    public function testBuildEmptyFilters(): void
    {
        $result = $this->builder->build([]);
        $this->assertSame(['', []], $result);
    }

    public function testBuildSimpleEquality(): void
    {
        $filters = ['status' => 1];
        [$where, $params] = $this->builder->build($filters);

        $this->assertSame(' WHERE `status` = :status', $where);
        $this->assertSame(['status' => 1], $params);
    }

    public function testBuildGreaterThan(): void
    {
        $filters = ['age' => ['>' => 18]];
        [$where, $params] = $this->builder->build($filters);

        $this->assertSame(' WHERE `age` > :age_GT', $where);
        $this->assertSame(['age_GT' => 18], $params);
    }

    public function testBuildInOperator(): void
    {
        $filters = ['id' => ['IN' => [1, 2, 3]]];
        [$where, $params] = $this->builder->build($filters);

        // Params are now using placeholders like :id_IN_0, :id_IN_1
        // We verify the structure roughly
        $this->assertStringContainsString('`id` IN (', $where);
        $this->assertCount(3, $params);
    }

    public function testBuildLikeOperator(): void
    {
        $filters = ['name' => ['LIKE' => '%John%']];
        [$where, $params] = $this->builder->build($filters);

        $this->assertSame(' WHERE `name` LIKE :name_LIKE', $where);
        $this->assertSame(['name_LIKE' => '%John%'], $params);
    }

    public function testInvalidColumnThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid SQL column name 'DROP'");
        $this->builder->build(['DROP' => 1]);
    }

    public function testInvalidOperatorThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->builder->build(['age' => ['INVALID' => 10]]);
    }
}
