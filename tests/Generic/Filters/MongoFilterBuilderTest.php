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
use Maatify\DataRepository\Generic\Support\MongoFilterBuilder;
use PHPUnit\Framework\TestCase;

class MongoFilterBuilderTest extends TestCase
{
    private MongoFilterBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new MongoFilterBuilder();
    }

    public function testBuildEmptyFilters(): void
    {
        $result = $this->builder->build([]);
        $this->assertSame([], $result);
    }

    public function testBuildSimpleEquality(): void
    {
        $filters = ['status' => 1];
        $result = $this->builder->build($filters);

        $this->assertSame(['status' => 1], $result);
    }

    public function testBuildGreaterThan(): void
    {
        $filters = ['age' => ['>' => 18]];
        $result = $this->builder->build($filters);

        $this->assertSame(['age' => ['$gt' => 18]], $result);
    }

    public function testBuildIdMapping(): void
    {
        $filters = ['id' => 123];
        $result = $this->builder->build($filters);

        $this->assertSame(['_id' => 123], $result);
    }

    public function testBuildLikeOperator(): void
    {
        $filters = ['name' => ['LIKE' => 'John']];
        $result = $this->builder->build($filters);

        $this->assertArrayHasKey('name', $result);
        $nameFilter = $result['name'];

        $this->assertIsArray($nameFilter);
        /** @var array<string, mixed> $nameFilter */
        $this->assertArrayHasKey('$regex', $nameFilter);
        $this->assertSame('John', $nameFilter['$regex']);
    }

    public function testInvalidFieldThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid Mongo field '\$where'");
        $this->builder->build(['$where' => 'javascript']);
    }

    public function testMergingConditions(): void
    {
        $filters = ['age' => ['>' => 18, '<' => 30]];
        $result = $this->builder->build($filters);

        $this->assertSame([
            'age' => [
                '$gt' => 18,
                '$lt' => 30,
            ],
        ], $result);
    }
}
