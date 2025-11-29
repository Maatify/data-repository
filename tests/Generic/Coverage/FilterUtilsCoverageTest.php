<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 09:10
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
    public function testBuildSqlWhereWithMultipleConditions(): void
    {
        $filters = [
            'id' => 1,
            'status' => 'active',
            'age' => ['>' => 18],
            'role' => ['IN' => ['admin', 'user']],
            'name' => ['LIKE' => '%John%'],
        ];

        [$where, $params] = FilterUtils::buildSqlWhere($filters);

        $this->assertStringContainsString('`id` = :', $where);
        $this->assertStringContainsString('`status` = :', $where);
        $this->assertStringContainsString('`age` > :', $where);
        $this->assertStringContainsString('`role` IN (:', $where);
        $this->assertStringContainsString('`name` LIKE :', $where);
        $this->assertCount(6, $params); // id, status, age, role1, role2, name
    }

    public function testBuildSqlWhereWithNull(): void
    {
        $filters = ['deleted_at' => null];
        [$where, $params] = FilterUtils::buildSqlWhere($filters);

        $this->assertStringContainsString('`deleted_at` IS NULL', $where);
        $this->assertEmpty($params);
    }

    public function testBuildSqlWhereWithIsNotNull(): void
    {
        $filters = ['deleted_at' => ['IS NOT NULL' => true]];
        [$where, $params] = FilterUtils::buildSqlWhere($filters);

        $this->assertStringContainsString('`deleted_at` IS NOT NULL', $where);
        $this->assertEmpty($params);
    }

    public function testBuildMongoFilterWithComplexConditions(): void
    {
        $filters = [
            'age' => ['>=' => 21],
            'name' => ['!=' => 'Bob'],
        ];

        $mongoFilter = FilterUtils::buildMongoFilter($filters);

        $this->assertArrayHasKey('age', $mongoFilter);

        /** @var array<string, mixed> $ageFilter */
        $ageFilter = $mongoFilter['age'];
        $this->assertArrayHasKey('$gte', $ageFilter);
        $this->assertEquals(21, $ageFilter['$gte']);

        $this->assertArrayHasKey('name', $mongoFilter);

        /** @var array<string, mixed> $nameFilter */
        $nameFilter = $mongoFilter['name'];
        $this->assertArrayHasKey('$ne', $nameFilter);
        $this->assertEquals('Bob', $nameFilter['$ne']);
    }

    public function testBuildMongoFilterWithInNotIn(): void
    {
        $filters = [
            'role' => ['IN' => [1, 2]],
            'status' => ['NOT IN' => ['banned']],
        ];

        $mongoFilter = FilterUtils::buildMongoFilter($filters);

        $this->assertArrayHasKey('role', $mongoFilter);

        /** @var array<string, mixed> $roleFilter */
        $roleFilter = $mongoFilter['role'];
        $this->assertArrayHasKey('$in', $roleFilter);
        $this->assertEquals([1, 2], $roleFilter['$in']);

        $this->assertArrayHasKey('status', $mongoFilter);

        /** @var array<string, mixed> $statusFilter */
        $statusFilter = $mongoFilter['status'];
        $this->assertArrayHasKey('$nin', $statusFilter);
        $this->assertEquals(['banned'], $statusFilter['$nin']);
    }
}
