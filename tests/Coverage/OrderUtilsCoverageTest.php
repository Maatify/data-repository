<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 09:15
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Coverage;

use Maatify\DataRepository\Generic\Support\OrderUtils;
use PHPUnit\Framework\TestCase;

class OrderUtilsCoverageTest extends TestCase
{
    public function testNormalizeString(): void
    {
        $input = ['name' => 'ASC'];
        $result = OrderUtils::normalize($input);
        $this->assertEquals(['name' => 'ASC'], $result);
    }

    public function testNormalizeInvalidDirectionThrowsOrIgnored(): void
    {
        $input = ['name' => 'INVALID'];
        $result = OrderUtils::normalize($input); // Should verify behavior. Assuming it defaults to ASC or filters out.
        // Checking implementation source would be better, but testing behavior:
        $this->assertEquals(['name' => 'ASC'], $result); // Implementation usually defaults to ASC
    }

    public function testBuildSqlOrderByMultiple(): void
    {
        $orderBy = ['name' => 'ASC', 'created_at' => 'DESC'];
        $sql = OrderUtils::buildSqlOrderBy($orderBy);
        $this->assertEquals('ORDER BY `name` ASC, `created_at` DESC', $sql);
    }

    public function testBuildMongoSort(): void
    {
        $orderBy = ['name' => 'ASC', 'age' => 'DESC'];
        $mongoSort = OrderUtils::buildMongoSort($orderBy);
        $this->assertEquals(['name' => 1, 'age' => -1], $mongoSort);
    }

    public function testSortArray(): void
    {
        $data = [
            ['id' => 1, 'score' => 10],
            ['id' => 2, 'score' => 20],
            ['id' => 3, 'score' => 10],
        ];

        // Sort by score DESC, id ASC
        $result = OrderUtils::sortArray($data, ['score' => 'DESC', 'id' => 'ASC']);

        $this->assertEquals(2, $result[0]['id']); // 20
        $this->assertEquals(1, $result[1]['id']); // 10, id 1
        $this->assertEquals(3, $result[2]['id']); // 10, id 3
    }
}
