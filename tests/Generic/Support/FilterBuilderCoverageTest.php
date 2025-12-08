<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Support;

use Maatify\DataRepository\Generic\Support\MongoFilterBuilder;
use Maatify\DataRepository\Generic\Support\MySQLFilterBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MongoFilterBuilder::class)]
#[CoversClass(MySQLFilterBuilder::class)]
class FilterBuilderCoverageTest extends TestCase
{
    private MongoFilterBuilder $mongoBuilder;
    private MySQLFilterBuilder $mysqlBuilder;

    protected function setUp(): void
    {
        $this->mongoBuilder = new MongoFilterBuilder();
        $this->mysqlBuilder = new MySQLFilterBuilder();
    }

    public static function filterProvider(): array
    {
        return [
            'equals' => [['id' => 1], ['_id' => 1], ' WHERE `id` = :id', ['id' => 1]],
            'greaterThan' => [['age' => ['>' => 25]], ['age' => ['$gt' => 25]], ' WHERE `age` > :age_GT', ['age_GT' => 25]],
            'lessThan' => [['price' => ['<' => 100]], ['price' => ['$lt' => 100]], ' WHERE `price` < :price_LT', ['price_LT' => 100]],
            'notEquals' => [['status' => ['!=' => 'active']], ['status' => ['$ne' => 'active']], ' WHERE `status` != :status_NEEQ', ['status_NEEQ' => 'active']],
            'in' => [['category' => ['IN' => [1, 2, 3]]], ['category' => ['$in' => [1, 2, 3]]], ' WHERE `category` IN (:category_IN_0, :category_IN_1, :category_IN_2)', ['category_IN_0' => 1, 'category_IN_1' => 2, 'category_IN_2' => 3]],
            'notIn' => [['tag' => ['NOT IN' => ['a', 'b']]], ['tag' => ['$nin' => ['a', 'b']]], ' WHERE `tag` NOT IN (:tag_NOT_IN_0, :tag_NOT_IN_1)', ['tag_NOT_IN_0' => 'a', 'tag_NOT_IN_1' => 'b']],
            'like' => [['name' => ['LIKE' => '%test%']], ['name' => ['$regex' => '.*test.*', '$options' => 'i']], ' WHERE `name` LIKE :name_LIKE', ['name_LIKE' => '%test%']],
            'between' => [['created_at' => ['BETWEEN' => ['2023-01-01', '2023-12-31']]], ['created_at' => ['$gte' => '2023-01-01', '$lte' => '2023-12-31']], ' WHERE `created_at` BETWEEN :created_at_BETWEEN_1 AND :created_at_BETWEEN_2', ['created_at_BETWEEN_1' => '2023-01-01', 'created_at_BETWEEN_2' => '2023-12-31']],
            'isNull' => [['deleted_at' => null], ['deleted_at' => null], ' WHERE `deleted_at` IS NULL', []],
            'isNotNull' => [['updated_at' => ['IS NOT NULL' => null]], ['updated_at' => ['$ne' => null]], ' WHERE `updated_at` IS NOT NULL', []],
        ];
    }

    #[DataProvider('filterProvider')]
    public function testBuild(array $filters, array $expectedMongo, string $expectedMysqlClause, array $expectedMysqlParams): void
    {
        $mongoResult = $this->mongoBuilder->build($filters);
        $this->assertEquals($expectedMongo, $mongoResult);

        [$mysqlClause, $mysqlParams] = $this->mysqlBuilder->build($filters);
        $this->assertEquals($expectedMysqlClause, $mysqlClause);
        $this->assertEquals($expectedMysqlParams, $mysqlParams);
    }

    public function testMultipleConditions(): void
    {
        $filters = [
            'age' => ['>' => 25, '<' => 50],
            'status' => 'active',
        ];

        $expectedMongo = [
            'age' => ['$gt' => 25, '$lt' => 50],
            'status' => 'active',
        ];

        $expectedMysqlClause = ' WHERE `age` > :age_GT AND `age` < :age_LT AND `status` = :status';
        $expectedMysqlParams = [
            'age_GT' => 25,
            'age_LT' => 50,
            'status' => 'active',
        ];

        $mongoResult = $this->mongoBuilder->build($filters);
        $this->assertEquals($expectedMongo, $mongoResult);

        [$mysqlClause, $mysqlParams] = $this->mysqlBuilder->build($filters);
        $this->assertEquals($expectedMysqlClause, $mysqlClause);
        $this->assertEquals($expectedMysqlParams, $mysqlParams);
    }
}
