<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Fake\Filters;

use InvalidArgumentException;
use Maatify\DataRepository\Generic\Support\FilterUtils;
use PHPUnit\Framework\TestCase;

class AdvancedFilterFakeTest extends TestCase
{
    // --- SQL WHERE Builder Tests ---

    public function testBuildSqlWhere_Empty(): void
    {
        [$sql, $params] = FilterUtils::buildSqlWhere([]);
        $this->assertEquals('', $sql);
        $this->assertEquals([], $params);
    }

    public function testBuildSqlWhere_Equality(): void
    {
        $filters = ['status' => 'active'];
        [$sql, $params] = FilterUtils::buildSqlWhere($filters);

        $this->assertStringContainsString('`status` = :status', $sql);
        $this->assertEquals(['status' => 'active'], $params);
    }

    public function testBuildSqlWhere_ExplicitIsNull(): void
    {
        $filters = ['deleted_at' => null];
        [$sql, $params] = FilterUtils::buildSqlWhere($filters);

        $this->assertStringContainsString('`deleted_at` IS NULL', $sql);
        $this->assertEmpty($params);
    }

    public function testBuildSqlWhere_InvalidColumnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid SQL column name 'bad-name'");
        FilterUtils::buildSqlWhere(['bad-name' => 1]);
    }

    public function testBuildSqlWhere_ReservedWordException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid SQL column name 'select'");
        FilterUtils::buildSqlWhere(['select' => 1]);
    }

    public function testBuildSqlWhere_UnsupportedOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        // Updated to match generic FilterParser message (was "Unsupported SQL operator...")
        $this->expectExceptionMessage("Unsupported operator 'XYZ'");
        FilterUtils::buildSqlWhere(['age' => ['XYZ' => 10]]);
    }

    public function testBuildSqlWhere_InOperator(): void
    {
        $filters = ['status' => ['IN' => ['active', 'pending']]];
        [$sql, $params] = FilterUtils::buildSqlWhere($filters);

        $this->assertStringContainsString('`status` IN (:status_IN_0, :status_IN_1)', $sql);
        $this->assertEquals('active', $params['status_IN_0']);
        $this->assertEquals('pending', $params['status_IN_1']);
    }

    public function testBuildSqlWhere_InOperator_Empty(): void
    {
        $filters = ['status' => ['IN' => []]];
        [$sql, $params] = FilterUtils::buildSqlWhere($filters);

        $this->assertStringContainsString('1=0', $sql); // False condition
    }

    public function testBuildSqlWhere_NotInOperator_Empty(): void
    {
        $filters = ['status' => ['NOT IN' => []]];
        [$sql, $params] = FilterUtils::buildSqlWhere($filters);

        $this->assertStringContainsString('1=1', $sql); // True condition
    }

    public function testBuildSqlWhere_InOperator_InvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        FilterUtils::buildSqlWhere(['status' => ['IN' => 'string']]);
    }

    public function testBuildSqlWhere_BetweenOperator(): void
    {
        $filters = ['age' => ['BETWEEN' => [18, 65]]];
        [$sql, $params] = FilterUtils::buildSqlWhere($filters);

        $this->assertStringContainsString('`age` BETWEEN :age_BETWEEN_1 AND :age_BETWEEN_2', $sql);
        $this->assertEquals(18, $params['age_BETWEEN_1']);
        $this->assertEquals(65, $params['age_BETWEEN_2']);
    }

    public function testBuildSqlWhere_BetweenOperator_Invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        FilterUtils::buildSqlWhere(['age' => ['BETWEEN' => [18]]]);
    }

    public function testBuildSqlWhere_LikeOperator(): void
    {
        $filters = ['name' => ['LIKE' => '%John%']];
        [$sql, $params] = FilterUtils::buildSqlWhere($filters);

        $this->assertStringContainsString('`name` LIKE :name_LIKE', $sql);
        $this->assertEquals('%John%', $params['name_LIKE']);
    }

    public function testBuildSqlWhere_IsNullOperator(): void
    {
        $filters = ['deleted_at' => ['IS NULL' => true]]; // Value ignored
        [$sql, $params] = FilterUtils::buildSqlWhere($filters);

        $this->assertStringContainsString('`deleted_at` IS NULL', $sql);
    }

    public function testBuildSqlWhere_IsNotNullOperator(): void
    {
        $filters = ['deleted_at' => ['IS NOT NULL' => true]];
        [$sql, $params] = FilterUtils::buildSqlWhere($filters);

        $this->assertStringContainsString('`deleted_at` IS NOT NULL', $sql);
    }

    // --- Mongo Filter Builder Tests ---

    public function testBuildMongoFilter_IdNormalization(): void
    {
        $filters = ['id' => 123];
        $mongo = FilterUtils::buildMongoFilter($filters);

        $this->assertArrayHasKey('_id', $mongo);
        $this->assertArrayNotHasKey('id', $mongo);
        $this->assertEquals(123, $mongo['_id']);
    }

    public function testBuildMongoFilter_InvalidField(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid Mongo field '\$where'"); // Escaped $ for test logic
        FilterUtils::buildMongoFilter(['$where' => 'js']);
    }

    public function testBuildMongoFilter_UnsupportedOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unsupported operator 'XYZ'");
        FilterUtils::buildMongoFilter(['age' => ['XYZ' => 10]]);
    }

    public function testBuildMongoFilter_LikeOperator(): void
    {
        $filters = ['name' => ['LIKE' => '%John%']];
        $mongo = FilterUtils::buildMongoFilter($filters);

        $nameFilter = $mongo['name'];
        $this->assertIsArray($nameFilter);

        /** @var array<string, string> $nameFilter */
        $this->assertEquals('.*John.*', $nameFilter['$regex']);
        $this->assertEquals('i', $nameFilter['$options']);
    }

    public function testBuildMongoFilter_BetweenOperator(): void
    {
        $filters = ['age' => ['BETWEEN' => [18, 65]]];
        $mongo = FilterUtils::buildMongoFilter($filters);

        $ageFilter = $mongo['age'];
        $this->assertIsArray($ageFilter);

        /** @var array<string, int> $ageFilter */
        $this->assertEquals(18, $ageFilter['$gte']);
        $this->assertEquals(65, $ageFilter['$lte']);
    }

    public function testBuildMongoFilter_IsNull(): void
    {
        $filters = ['deleted_at' => ['IS NULL' => true]];
        $mongo = FilterUtils::buildMongoFilter($filters);

        $this->assertNull($mongo['deleted_at']);
    }

    public function testBuildMongoFilter_IsNotNull(): void
    {
        $filters = ['deleted_at' => ['IS NOT NULL' => true]];
        $mongo = FilterUtils::buildMongoFilter($filters);

        $deletedFilter = $mongo['deleted_at'];
        $this->assertIsArray($deletedFilter);

        /** @var array<string, mixed> $deletedFilter */
        $this->assertEquals(['$ne' => null], $deletedFilter);
    }

    public function testBuildMongoFilter_MergeConditions(): void
    {
        // Test merging multiple operators for same field
        $filters = ['age' => ['>' => 18, '<' => 30]];
        $mongo = FilterUtils::buildMongoFilter($filters);

        $ageFilter = $mongo['age'];
        $this->assertIsArray($ageFilter);

        /** @var array<string, int> $ageFilter */
        $this->assertEquals(18, $ageFilter['$gt']);
        $this->assertEquals(30, $ageFilter['$lt']);
    }
}
