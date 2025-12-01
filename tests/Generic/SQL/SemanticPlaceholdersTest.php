<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\SQL;

use Maatify\DataRepository\Generic\Support\FilterUtils;
use PHPUnit\Framework\TestCase;

/**
 * Validates semantic placeholders in generated SQL.
 */
class SemanticPlaceholdersTest extends TestCase
{
    public function testSimpleEqualityUsesSemanticPlaceholder(): void
    {
        $filters = ['name' => 'John'];
        [$sql, $params] = FilterUtils::buildSqlWhere($filters);

        // Expect: WHERE `name` = :name
        $this->assertStringContainsString('`name` = :name', $sql);
        $this->assertArrayHasKey('name', $params);
        $this->assertEquals('John', $params['name']);
    }

    public function testMultipleFiltersUseSemanticPlaceholders(): void
    {
        $filters = ['status' => 'active', 'type' => 1];
        [$sql, $params] = FilterUtils::buildSqlWhere($filters);

        // Expect: `status` = :status AND `type` = :type
        $this->assertStringContainsString('`status` = :status', $sql);
        $this->assertStringContainsString('`type` = :type', $sql);
        $this->assertEquals('active', $params['status']);
        $this->assertEquals(1, $params['type']);
    }

    public function testOperatorsUseSemanticSuffixes(): void
    {
        $filters = ['age' => ['>' => 18]];
        [$sql, $params] = FilterUtils::buildSqlWhere($filters);

        // Expect: `age` > :age_GT
        $this->assertStringContainsString('`age` > :age_GT', $sql);
        $this->assertArrayHasKey('age_GT', $params);
        $this->assertEquals(18, $params['age_GT']);
    }

    public function testComplexMix(): void
    {
        $filters = [
            'id' => ['IN' => [1, 2, 3]],
            'created_at' => ['BETWEEN' => ['2023-01-01', '2023-01-31']],
            'deleted' => null
        ];

        [$sql, $params] = FilterUtils::buildSqlWhere($filters);

        // IN
        $this->assertStringContainsString('`id` IN (:id_IN_0, :id_IN_1, :id_IN_2)', $sql);

        // BETWEEN
        $this->assertStringContainsString('`created_at` BETWEEN :created_at_BETWEEN_1 AND :created_at_BETWEEN_2', $sql);

        // IS NULL
        $this->assertStringContainsString('`deleted` IS NULL', $sql);

        $this->assertEquals(1, $params['id_IN_0']);
        $this->assertEquals('2023-01-01', $params['created_at_BETWEEN_1']);
    }
}
