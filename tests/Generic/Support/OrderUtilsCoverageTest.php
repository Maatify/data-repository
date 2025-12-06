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

use DateTime;
use Maatify\DataRepository\Generic\Support\OrderUtils;
use PHPUnit\Framework\TestCase;

class OrderUtilsCoverageTest extends TestCase
{
    public function testNormalize(): void
    {
        $orderBy = ['name' => 'asc', 'age' => 'DESC'];
        $result = OrderUtils::normalize($orderBy);

        $this->assertEquals(['name' => 'ASC', 'age' => 'DESC'], $result);
    }

    public function testBuildSqlOrderBy(): void
    {
        $orderBy = ['name' => 'ASC'];
        $sql = OrderUtils::buildSqlOrderBy($orderBy);

        $this->assertStringContainsString('ORDER BY `name` ASC', $sql);
    }

    public function testBuildJsonOrderBy(): void
    {
        $sql = OrderUtils::buildJsonOrderBy('data', '$.score', 'DESC');

        $this->assertStringContainsString('ORDER BY', $sql);
        $this->assertStringContainsString('JSON_UNQUOTE(JSON_EXTRACT(`data`, "$.score")) DESC', $sql);
    }

    public function testBuildMongoSort(): void
    {
        $orderBy = ['name' => 'ASC', 'age' => 'DESC'];
        $sort = OrderUtils::buildMongoSort($orderBy);

        $this->assertEquals(['name' => 1, 'age' => -1], $sort);
    }

    public function testFromString(): void
    {
        $orderString = "name:ASC, age:DESC";
        $result = OrderUtils::fromString($orderString);

        $this->assertEquals(['name' => 'ASC', 'age' => 'DESC'], $result);
    }

    public function testFromStringWithDefaults(): void
    {
        $orderString = "name, age";
        $result = OrderUtils::fromString($orderString);

        $this->assertEquals(['name' => 'ASC', 'age' => 'ASC'], $result);
    }

    public function testReverse(): void
    {
        $orderBy = ['name' => 'ASC', 'age' => 'DESC'];
        $result = OrderUtils::reverse($orderBy);

        $this->assertEquals(['name' => 'DESC', 'age' => 'ASC'], $result);
    }

    public function testMerge(): void
    {
        $o1 = ['name' => 'ASC'];
        $o2 = ['age' => 'DESC'];
        $o3 = ['name' => 'DESC']; // Should override o1

        $result = OrderUtils::merge($o1, $o2, $o3);

        // name should be DESC because o3 overrides o1
        $this->assertEquals(['name' => 'DESC', 'age' => 'DESC'], $result);
    }

    public function testSortArray(): void
    {
        $data = [
            ['id' => 1, 'score' => 10],
            ['id' => 2, 'score' => 30],
            ['id' => 3, 'score' => 20],
        ];

        $sorted = OrderUtils::sortArray($data, ['score' => 'DESC']);

        $this->assertEquals(2, $sorted[0]['id']); // 30
        $this->assertEquals(3, $sorted[1]['id']); // 20
        $this->assertEquals(1, $sorted[2]['id']); // 10
    }

    public function testSortArrayMultiColumn(): void
    {
        $data = [
            ['group' => 'A', 'score' => 10],
            ['group' => 'B', 'score' => 20],
            ['group' => 'A', 'score' => 30],
        ];

        // Sort by group ASC, then score DESC
        $sorted = OrderUtils::sortArray($data, ['group' => 'ASC', 'score' => 'DESC']);

        // A, 30
        $this->assertEquals('A', $sorted[0]['group']);
        $this->assertEquals(30, $sorted[0]['score']);

        // A, 10
        $this->assertEquals('A', $sorted[1]['group']);
        $this->assertEquals(10, $sorted[1]['score']);

        // B, 20
        $this->assertEquals('B', $sorted[2]['group']);
        $this->assertEquals(20, $sorted[2]['score']);
    }

    public function testSortArrayWithNulls(): void
    {
        $data = [
            ['val' => 10],
            ['val' => null],
            ['val' => 5],
        ];

        // ASC: nulls come first (-1)
        $sorted = OrderUtils::sortArray($data, ['val' => 'ASC']);
        $this->assertNull($sorted[0]['val']);
        $this->assertEquals(5, $sorted[1]['val']);
        $this->assertEquals(10, $sorted[2]['val']);

        // DESC: nulls come last (1) (Wait, implementation check needed)
        // Implementation: compareNulls returns -1 if a is null, 1 if b is null.
        // If sorting DESC, we invert the result.
        // If a=null, b=5: compareNulls returns -1. DESC -> return 1. So a > b? No, 1 means a comes AFTER b.
        // So nulls should be at the end in DESC?
        // Let's verify logic:
        // $cmp = -1 (a < b).
        // if DESC: return -$cmp = 1 (a > b). So a comes after b.
        // So nulls are considered "smaller" than values. In ASC they are first. In DESC they are last.

        $sortedDesc = OrderUtils::sortArray($data, ['val' => 'DESC']);
        $this->assertEquals(10, $sortedDesc[0]['val']);
        $this->assertEquals(5, $sortedDesc[1]['val']);
        $this->assertNull($sortedDesc[2]['val']);
    }

    public function testSortArrayWithDates(): void
    {
        $d1 = new DateTime('2023-01-01');
        $d2 = new DateTime('2023-01-02');

        $data = [
            ['date' => $d2],
            ['date' => $d1],
        ];

        $sorted = OrderUtils::sortArray($data, ['date' => 'ASC']);
        $this->assertSame($d1, $sorted[0]['date']);
        $this->assertSame($d2, $sorted[1]['date']);
    }

    public function testSortArrayWithStrings(): void
    {
        $data = [
            ['name' => 'Bob'],
            ['name' => 'Alice'],
        ];

        $sorted = OrderUtils::sortArray($data, ['name' => 'ASC']);
        $this->assertEquals('Alice', $sorted[0]['name']);
        $this->assertEquals('Bob', $sorted[1]['name']);
    }
}
