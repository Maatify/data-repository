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

namespace Maatify\DataRepository\Tests\Generic\Support;

use Maatify\DataRepository\Generic\Support\FilterUtils;
use PHPUnit\Framework\TestCase;

class FilterUtilsCoverageTest extends TestCase
{
    public function testBuildSqlWhere(): void
    {
        // Simple test to ensure facade delegates correctly
        // Complex logic is tested in MySQLFilterBuilderTest
        $filters = ['status' => 1];
        $result = FilterUtils::buildSqlWhere($filters);

        $this->assertCount(2, $result);
        $this->assertStringContainsString('WHERE', $result[0]);
        $this->assertStringContainsString('`status` = :status', $result[0]);
        $this->assertArrayHasKey('status', $result[1]);
        $this->assertEquals(1, $result[1]['status']);
    }

    public function testBuildMongoFilter(): void
    {
        // Simple test to ensure facade delegates correctly
        // Complex logic is tested in MongoFilterBuilderTest
        $filters = ['status' => 1];
        $result = FilterUtils::buildMongoFilter($filters);

        $this->assertArrayHasKey('status', $result);
        $this->assertEquals(1, $result['status']);
    }
}
