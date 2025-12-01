<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 04:10
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Coverage;

use Maatify\DataRepository\Generic\Support\FilterUtils;
use PHPUnit\Framework\TestCase;

class FilterUtilsCoverageTest extends TestCase
{
    public function testBuildSqlWhereWithEmptyFilters(): void
    {
        $result = FilterUtils::buildSqlWhere([]);
        $this->assertEquals(['', []], $result);
    }

    public function testBuildSqlWhereWithSimpleEquality(): void
    {
        $result = FilterUtils::buildSqlWhere(['id' => 1]);
        // Implementation generates keys without leading colon for the params array
        $this->assertEquals([' WHERE `id` = :id', ['id' => 1]], $result);
    }

    public function testBuildSqlWhereWithComplexConditions(): void
    {
        // Greater Than
        $result = FilterUtils::buildSqlWhere(['age' => ['>' => 18]]);
        // Implementation generates keys without leading colon for the params array
        $this->assertEquals([' WHERE `age` > :age_GT', ['age_GT' => 18]], $result);

        // IN
        $result = FilterUtils::buildSqlWhere(['status' => ['IN' => [1, 2]]]);
        $this->assertStringContainsString('IN (:status_IN_0, :status_IN_1)', $result[0]);
    }

    public function testBuildMongoFilter(): void
    {
        $this->assertEquals([], FilterUtils::buildMongoFilter([]));
        $this->assertEquals(['a' => 1], FilterUtils::buildMongoFilter(['a' => 1]));

        // Operators
        $this->assertEquals(['a' => ['$gt' => 1]], FilterUtils::buildMongoFilter(['a' => ['>' => 1]]));
        $this->assertEquals(['a' => ['$in' => [1, 2]]], FilterUtils::buildMongoFilter(['a' => ['IN' => [1, 2]]]));

        // ID Mapping
        $oid = new \MongoDB\BSON\ObjectId();
        $strOid = (string)$oid;
        $result = FilterUtils::buildMongoFilter(['id' => $strOid]);
        $this->assertEquals(['_id' => $oid], $result); // Should convert id to _id and string to ObjectId

        // NOT EQUAL
        $this->assertEquals(['a' => ['$ne' => 1]], FilterUtils::buildMongoFilter(['a' => ['!=' => 1]]));

        // NOT IN
        $this->assertEquals(['a' => ['$nin' => [1, 2]]], FilterUtils::buildMongoFilter(['a' => ['NOT IN' => [1, 2]]]));
    }
}
